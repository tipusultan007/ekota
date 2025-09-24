<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index(Request $request)
    {
        $query = Expense::with('category', 'user');

        // ফিল্টার প্রয়োগ করুন
        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        }

        $expenses = $query->latest()->paginate(15);

         $accounts = Account::active()->payment()->get();

        // ফর্ম এবং ফিল্টারের জন্য ক্যাটাগরির তালিকা
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.expenses.index', compact('expenses', 'categories', 'accounts'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        return view('admin.expenses.create', compact('categories'));
    }

       public function store(Request $request)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $paymentAccount = Account::findOrFail($request->account_id);
        $amount = $request->amount;

        if ($paymentAccount->balance < $amount) {
            return back()->with('error', 'Insufficient balance in the selected account.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $paymentAccount, $amount) {
                // ১. মূল খরচের রেকর্ড তৈরি করুন
                $expense = Expense::create($request->except(['receipt', 'account_id']));

                if ($request->hasFile('receipt')) {
                    $expense->addMediaFromRequest('receipt')->toMediaCollection('expense_receipts');
                }

                // ২. অ্যাকাউন্টিং সার্ভিস ব্যবহার করে ডাবল-এন্ট্রি লেনদেন তৈরি করুন
                $expenseCategory = ExpenseCategory::find($request->expense_category_id);
                // খরচের খাতটিকে একটি Expense Account হিসেবে ধরতে হবে (Chart of Accounts-এ)
                $expenseGLAccount = Account::where('name', $expenseCategory->name)->where('type', 'Expense')->firstOrFail();

                $this->accountingService->createTransaction(
                    $request->expense_date,
                    'Expense: ' . $expenseCategory->name . ($request->description ? ' - ' . $request->description : ''),
                    [
                        ['account_id' => $expenseGLAccount->id, 'debit' => $amount], // Debit Expense (Expense increases)
                        ['account_id' => $paymentAccount->id, 'credit' => $amount], // Credit Cash/Bank (Asset decreases)
                    ],
                    $expense
                );
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.expenses.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }
        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully.');
    }


    public function edit(Expense $expense)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        // ধাপ ১: ফর্মের ড্রপডাউনের জন্য প্রয়োজনীয় ডেটা প্রস্তুত করুন
        
        // খরচের খাতের তালিকা
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        
        // পেমেন্ট অ্যাকাউন্টের তালিকা (সক্রিয় এবং পেমেন্টের জন্য ব্যবহৃত)
        $accounts = Account::active()->payment()->orderBy('name')->get();

        // ধাপ ২: এই খরচের সাথে সম্পর্কিত বিদ্যমান লেনদেনটি খুঁজুন
        $transaction = $expense->transactions()->first();
        
        // যদি কোনো কারণে লেনদেন না থাকে (পুরানো ডেটার ক্ষেত্রে হতে পারে)
        // তাহলে currentPaymentAccountId null থাকবে
        $currentPaymentAccountId = $transaction ? $transaction->journalEntries()->whereNotNull('credit')->first()->account_id : null;

        return view('admin.expenses.edit', compact(
            'expense', 
            'categories', 
            'accounts', 
            'currentPaymentAccountId'
        ));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $expense) {
                // --- ১. পুরানো অ্যাকাউন্টিং লেনদেন রিভার্স করুন ---
                $oldTransaction = $expense->transactions()->first();
                if ($oldTransaction) {
                    $this->reverseTransaction($oldTransaction);
                }

                // --- ২. খরচের মূল রেকর্ডটি আপডেট করুন ---
                $expense->update($request->except(['receipt', 'account_id']));
                if ($request->hasFile('receipt')) {
                    $expense->clearMediaCollection('expense_receipts');
                    $expense->addMediaFromRequest('receipt')->toMediaCollection('expense_receipts');
                }

                // --- ৩. নতুন অ্যাকাউন্টিং এন্ট্রি দিন ---
                $paymentAccount = Account::findOrFail($request->account_id);
                $expenseCategory = ExpenseCategory::find($request->expense_category_id);
                $expenseGLAccount = Account::where('name', $expenseCategory->name)->where('type', 'Expense')->firstOrFail();

                $this->accountingService->createTransaction(
                    $request->expense_date,
                    'Expense: ' . $expenseCategory->name . ' (Updated)',
                    [
                        ['account_id' => $expenseGLAccount->id, 'debit' => $request->amount],
                        ['account_id' => $paymentAccount->id, 'credit' => $request->amount],
                    ],
                    $expense
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
    }


    public function destroy(Expense $expense)
    {
        // নিরাপত্তা যাচাই: শুধুমাত্র অ্যাডমিন
        if (!Auth::user()->hasRole('Admin')) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        try {
            DB::transaction(function () use ($expense) {
                // --- ১. অ্যাকাউন্টিং লেনদেন রিভার্স করুন ---
                $transaction = $expense->transactions()->first();
                if ($transaction) {
                    $this->reverseTransaction($transaction);
                }

                // --- ২. মিডিয়া এবং মূল রেকর্ড ডিলিট করুন ---
                $expense->clearMediaCollection('expense_receipts');
                $expense->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.expenses.index')->with('error', 'An error occurred while deleting the expense: ' . $e->getMessage());
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted and account balance restored successfully.');
    }

    private function reverseTransaction(\App\Models\Transaction $transaction)
    {
        foreach ($transaction->journalEntries as $entry) {
            $account = $entry->account;
            if ($entry->debit > 0) $account->handleCredit($entry->debit);
            if ($entry->credit > 0) $account->handleDebit($entry->credit);
        }
        $transaction->journalEntries()->delete();
        $transaction->delete();
    }
}
