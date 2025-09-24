<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LoanInstallment;
use App\Models\Salary;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SalaryController extends Controller
{
    use \App\Http\Controllers\Traits\TransactionReversalTrait;

    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Display a listing of the resource.
     * বেতনের সকল লেনদেনের তালিকা দেখাবে।
     */
    public function index(Request $request)
    {
        $query = Salary::with('user', 'processedBy');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('salary_month')) {
            // YYYY-MM format থেকে মাস ও বছর আলাদা করুন
            $date = \Carbon\Carbon::parse($request->salary_month);
            $query->whereYear('payment_date', $date->year)->whereMonth('payment_date', $date->month);
        }

        $salaries = $query->latest()->paginate(20);
        $employees = User::orderBy('name')->get();

        return view('admin.salaries.index', compact('salaries', 'employees'));
    }

    /**
     * Show the form for creating a new resource.
     * বেতন প্রদানের ফর্ম দেখাবে।
     */
    public function create()
    {
        $employees = User::where('status', 'active')->orderBy('name')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();

        return view('admin.salaries.create', compact('employees','accounts'));
    }

    /**
     * Store a newly created resource in storage.
     * নতুন বেতন প্রদান রেকর্ড সেভ করবে।
     */
     public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'salary_month' => 'required|string',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
        ]);

        $employee = User::find($request->user_id);
        $paymentAccount = Account::findOrFail($request->account_id);
        $amount = $request->amount;
        $salaryMonth = \Carbon\Carbon::parse($request->salary_month)->format('F, Y');
        
        if ($paymentAccount->balance < $amount) {
            return back()->with('error', 'Insufficient balance in the payment account.');
        }
        if (Salary::where('user_id', $employee->id)->where('salary_month', $salaryMonth)->exists()) {
            return back()->with('error', 'Salary for this month has already been paid.');
        }

        try {
            DB::transaction(function () use ($request, $employee, $paymentAccount, $amount, $salaryMonth) {
                
                $salary = Salary::create([
                    'user_id' => $employee->id,
                    'processed_by_user_id' => Auth::id(),
                    'amount' => $amount,
                    'salary_month' => $salaryMonth,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes,
                ]);

                
                $salaryExpenseAccount = Account::where('code', '5010')->firstOrFail(); 
                
                $this->accountingService->createTransaction(
                    $request->payment_date,
                    'Salary paid to ' . $employee->name . ' for ' . $salaryMonth,
                    [
                        ['account_id' => $salaryExpenseAccount->id, 'debit' => $amount], 
                        ['account_id' => $paymentAccount->id, 'credit' => $amount],   
                    ],
                    $salary
                );
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.salaries.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
        return redirect()->route('admin.salaries.index')->with('success', 'Salary paid and recorded successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * বেতনের এন্ট্রি সম্পাদনা করার ফর্ম দেখাবে।
     */
    public function edit(Salary $salary)
    {
        $employees = User::where('salary', '>', 0)->where('status', 'active')->orderBy('name')->get();
        $accounts = Account::active()->payment()->orderBy('name')->get();

        return view('admin.salaries.edit', compact('salary', 'employees','accounts'));
    }

    /**
     * Update the specified resource in storage.
     * বেতনের এন্ট্রি আপডেট করবে।
     */
    // app/Http-Controllers/Admin/SalaryController.php


  public function update(Request $request, Salary $salary)
    {
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $salary) {
                // --- ১. পুরানো অ্যাকাউন্টিং লেনদেন রিভার্স করুন ---
                $oldTransaction = $salary->transactions()->first();
                if ($oldTransaction) {
                    $this->reverseTransaction($oldTransaction);
                }
                
                // --- ২. Salary রেকর্ডটি আপডেট করুন ---
                $salary->update($request->except('account_id'));

                // --- ৩. নতুন অ্যাকাউন্টিং এন্ট্রি দিন ---
                $paymentAccount = Account::findOrFail($request->account_id);
                $salaryExpenseAccount = Account::where('code', '5010')->firstOrFail();
                
                $this->accountingService->createTransaction(
                    $request->payment_date,
                    'Salary paid to ' . $salary->user->name . ' for ' . $salary->salary_month . ' (Updated)',
                    [
                        ['account_id' => $salaryExpenseAccount->id, 'debit' => $request->amount],
                        ['account_id' => $paymentAccount->id, 'credit' => $request->amount],
                    ],
                    $salary
                );
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update salary record: ' . $e->getMessage())->withInput();
        }
        return redirect()->route('admin.salaries.index')->with('success', 'Salary record updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     * বেতনের এন্ট্রি ডিলিট করবে।
     */

    public function destroy(Salary $salary)
    {
         if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($salary) {
                // --- ১. অ্যাকাউন্টিং লেনদেন রিভার্স করুন ---
                $transaction = $salary->transactions()->first();
                if ($transaction) {
                    $this->reverseTransaction($transaction);
                }
                
                // --- ২. মূল Salary রেকর্ডটি ডিলিট করুন ---
                $salary->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.salaries.index')->with('error', 'Failed to delete salary record: ' . $e->getMessage());
        }
        return redirect()->route('admin.salaries.index')->with('success', 'Salary record deleted and balance restored.');
    }
    
  

}
