<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\LoanInstallment;
use App\Models\Salary;
use App\Models\User;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class SalaryController extends Controller
{
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
        $accounts = Account::where('is_active', true)->get();
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
        $salaryMonth = \Carbon\Carbon::parse($request->salary_month)->format('F, Y');

        $alreadyPaid = Salary::where('user_id', $employee->id)->where('salary_month', $salaryMonth)->exists();
        if ($alreadyPaid) {
            return back()->with('error', 'Salary for this month has already been paid to ' . $employee->name);
        }

        DB::transaction(function () use ($request, $employee, $salaryMonth) {
            $account = Account::findOrFail($request->account_id);
            $salary = Salary::create([
                'user_id' => $employee->id,
                'account_id' => $request->account_id,
                'processed_by_user_id' => Auth::id(),
                'amount' => $request->amount,
                'salary_month' => $salaryMonth,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            $salaryExpenseCategory = ExpenseCategory::firstOrCreate(['name' => 'Employee Salary']);

            // Expense রেকর্ড তৈরি করুন যা Salary-এর সাথে যুক্ত
            $expense = $salary->expense()->create([
                'expense_category_id' => $salaryExpenseCategory->id,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'expense_date' => $request->payment_date,
                'description' => 'Salary for ' . $salaryMonth,
            ]);

            // Transaction রেকর্ড তৈরি করুন যা Expense-এর সাথে যুক্ত
            $paymentAccount = Account::find($request->account_id);
            $expense->transactions()->create([
                'account_id' => $paymentAccount->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => 'Salary paid to ' . $employee->name,
                'transaction_date' => $request->payment_date,
            ]);
        });

        return redirect()->route('admin.salaries.index')->with('success', 'Salary paid successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     * বেতনের এন্ট্রি সম্পাদনা করার ফর্ম দেখাবে।
     */
    public function edit(Salary $salary)
    {
        $employees = User::where('salary', '>', 0)->where('status', 'active')->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->get();
        return view('admin.salaries.edit', compact('salary', 'employees','accounts'));
    }

    /**
     * Update the specified resource in storage.
     * বেতনের এন্ট্রি আপডেট করবে।
     */
    // app/Http-Controllers/Admin/SalaryController.php

    public function update(Request $request, Salary $salary)
    {
        // নিরাপত্তা যাচাই
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'account_id' => 'required|exists:accounts,id', // কোন অ্যাকাউন্ট থেকে পেমেন্ট হচ্ছে
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $salary) {

                // সংশ্লিষ্ট Expense এবং Transaction রেকর্ডগুলো খুঁজুন
                $expense = $salary->expense;
                if (!$expense) {
                    throw new \Exception('Associated expense record not found.');
                }
                $transaction = $expense->transactions()->where('type', 'debit')->first();
                if (!$transaction) {
                    throw new \Exception('Associated transaction record not found.');
                }

                $oldAmount = $salary->amount;
                $newAmount = $request->amount;
                $oldPaymentAccount = $transaction->account;
                $newPaymentAccount = Account::find($request->account_id);

                // --- ধাপ ২: রেকর্ডগুলো আপডেট করুন ---
                // ক) Salary রেকর্ড
                $salary->update([
                    'amount' => $newAmount,
                    'payment_date' => $request->payment_date,
                    'notes' => $request->notes,
                    'processed_by_user_id' => Auth::id(),
                ]);

                // খ) Expense রেকর্ড
                $expense->update([
                    'amount' => $newAmount,
                    'expense_date' => $request->payment_date,
                    'description' => 'Salary paid to ' . $salary->user->name . ' for ' . $salary->salary_month . ' (Updated)',
                ]);

                // গ) Transaction রেকর্ড
                $transaction->update([
                    'account_id' => $newPaymentAccount->id,
                    'amount' => $newAmount,
                    'transaction_date' => $request->payment_date,
                ]);

            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update salary record. Error: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.salaries.index')->with('success', 'Salary record and associated transactions updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * বেতনের এন্ট্রি ডিলিট করবে।
     */
    public function destroy(Salary $salary)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($salary) {

                // ধাপ ১: বেতনের সাথে সম্পর্কিত Expense রেকর্ডটি খুঁজুন
                $expense = $salary->expense;

                if ($expense) {
                    // ধাপ ২: Expense-এর সাথে সম্পর্কিত Transaction রেকর্ডটি খুঁজুন
                    $transaction = $expense->transactions()->where('type', 'debit')->first();

                    if ($transaction) {
                        // ধাপ ৩: আর্থিক অ্যাকাউন্টের (ক্যাশ/ব্যাংক) ব্যালেন্স পুনরুদ্ধার করুন
                        $paymentAccount = $transaction->account;
                        $paymentAccount->increment('balance', $salary->amount);

                        // ধাপ ৪: Transaction রেকর্ডটি ডিলিট করুন
                        $transaction->delete();
                    }

                    // ধাপ ৫: Expense রেকর্ডটি ডিলিট করুন
                    $expense->delete();
                }

                // ধাপ ৬: সবশেষে, মূল Salary রেকর্ডটি ডিলিট করুন
                $salary->delete();

            });
        } catch (\Exception $e) {
            return redirect()->route('admin.salaries.index')->with('error', 'Failed to delete salary record. Error: ' . $e->getMessage());
        }

        return redirect()->route('admin.salaries.index')->with('success', 'Salary record and associated transactions have been deleted. Balance restored.');
    }
}
