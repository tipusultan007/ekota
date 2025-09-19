<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
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

        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // ফর্ম এবং ফিল্টারের জন্য ক্যাটাগরির তালিকা
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.expenses.index', compact('expenses', 'categories','accounts'));
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
            'account_id' => 'required|exists:accounts,id', // কোন অ্যাকাউন্ট থেকে টাকা যাচ্ছে
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $account = Account::findOrFail($request->account_id);
        $amount = $request->amount;

        // অ্যাকাউন্টে পর্যাপ্ত ব্যালেন্স আছে কিনা তা যাচাই করুন
        if ($account->balance < $amount) {
            return back()->with('error', 'Insufficient balance in the selected account.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $account, $amount) {
                // ধাপ ১: expenses টেবিলে মূল খরচের রেকর্ড তৈরি করুন
                // পলিমরফিক কলামগুলো আপাতত null থাকবে, কারণ এটি কোনো নির্দিষ্ট মডেলের সাথে যুক্ত নয়
                $expense = Expense::create([
                    'expense_category_id' => $request->expense_category_id,
                    'user_id' => Auth::id(),
                    'amount' => $amount,
                    'expense_date' => $request->expense_date,
                    'description' => $request->description,
                    'expensable_id' => null, // এটি কোনো Salary বা Transfer নয়
                    'expensable_type' => null,
                ]);

                // ধাপ ২: transactions টেবিলে একটি ডেবিট (খরচ) লেনদেন তৈরি করুন
                $account->transactions()->create([
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => 'Expense: ' . $expense->category->name . ($request->description ? ' - ' . $request->description : ''),
                    'transaction_date' => $request->expense_date,
                    'transactionable'
                ]);

                // ধাপ ৩: অ্যাকাউন্ট থেকে ব্যালেন্স বিয়োগ করুন
                $account->decrement('balance', $amount);

                // ধাপ ৪: যদি রশিদ আপলোড করা হয়, তাহলে সেটি যোগ করুন
                if ($request->hasFile('receipt')) {
                    $expense->addMediaFromRequest('receipt')->toMediaCollection('expense_receipts');
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.expenses.index')->with('error', 'An error occurred: ' . $e->getMessage());
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded and account balance updated successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();
        $accounts = Account::where('is_active', true)->orderBy('name')->get();

        // খরচের সাথে সম্পর্কিত লেনদেনটি খুঁজুন
        $transaction = $expense->transactions()->where('type', 'debit')->first();

        return view('admin.expenses.edit', compact('expense', 'categories', 'accounts', 'transaction'));
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
                // সংশ্লিষ্ট লেনদেনটি খুঁজুন
                $transaction = $expense->transactions()->where('type', 'debit')->first();
                if (!$transaction) {
                    // যদি কোনো কারণে লেনদেন না থাকে, একটি এরর দেখান
                    throw new \Exception('Associated transaction not found for this expense.');
                }

                $oldAmount = $transaction->amount;
                $oldAccount = $transaction->account;

                $newAmount = $request->amount;
                $newAccount = Account::find($request->account_id);

                // ধাপ ১: পুরানো অ্যাকাউন্ট ব্যালেন্স পুনরুদ্ধার করুন
                $oldAccount->increment('balance', $oldAmount);

                // ধাপ ২: নতুন অ্যাকাউন্ট থেকে ব্যালেন্স বিয়োগ করুন
                if ($newAccount->balance < $newAmount) {
                    throw new \Exception('Insufficient balance in the new selected account.');
                }
                $newAccount->decrement('balance', $newAmount);

                // ধাপ ৩: খরচের মূল রেকর্ডটি আপডেট করুন
                $expense->update($request->except(['receipt', 'account_id']));

                // ধাপ ৪: লেনদেন রেকর্ডটি আপডেট করুন
                $transaction->update([
                    'account_id' => $newAccount->id,
                    'amount' => $newAmount,
                    'description' => 'Expense: ' . $expense->category->name . ($request->description ? ' - ' . $request->description : ''),
                    'transaction_date' => $request->expense_date,
                ]);

                // ধাপ ৫: রশিদ আপডেট করুন (যদি থাকে)
                if ($request->hasFile('receipt')) {
                    $expense->clearMediaCollection('expense_receipts');
                    $expense->addMediaFromRequest('receipt')->toMediaCollection('expense_receipts');
                }
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
                // ধাপ ১: খরচের সাথে সম্পর্কিত লেনদেনটি খুঁজুন
                $transaction = $expense->transactions()->where('type', 'debit')->first();

                // যদি লেনদেন পাওয়া যায়, তাহলে ব্যালেন্স পুনরুদ্ধার করুন
                if ($transaction) {
                    $account = $transaction->account;
                    // ধাপ ২: অ্যাকাউন্টে ডিলিট করা খরচের পরিমাণ ফেরত দিন (ব্যালেন্স বাড়ান)
                    $account->increment('balance', $transaction->amount);

                    // ধাপ ৩: লেনদেন রেকর্ডটি ডিলিট করুন
                    $transaction->delete();
                }

                // ধাপ ৪: খরচের সাথে সম্পর্কিত যেকোনো মিডিয়া (রশিদ) ডিলিট করুন
                $expense->clearMediaCollection('expense_receipts');

                // ধাপ ৫: সবশেষে, খরচের মূল রেকর্ডটি ডিলিট করুন
                $expense->delete();
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.expenses.index')->with('error', 'An error occurred while deleting the expense: ' . $e->getMessage());
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted and account balance restored successfully.');
    }
}
