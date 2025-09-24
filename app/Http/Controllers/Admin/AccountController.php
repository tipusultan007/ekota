<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * সকল আর্থিক অ্যাকাউন্টের তালিকা দেখাবে।
     */
    public function index()
    {
        // withSum এখন journalEntries রিলেশনশিপের উপর কাজ করবে
        $accounts = Account::latest()
            ->withSum('journalEntries as total_debits', 'debit')
            ->withSum('journalEntries as total_credits', 'credit')
            ->paginate(25);

        return view('admin.accounts.index', compact('accounts'));
    }

    /**
     * Show the form for creating a new resource.
     * নতুন অ্যাকাউন্ট তৈরির ফর্ম দেখাবে।
     */
    public function create()
    {
        return view('admin.accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     * নতুন অ্যাকাউন্ট তৈরি করে ডাটাবেসে সেভ করবে।
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:accounts,code',
            'name' => 'required|string|max:255|unique:accounts,name',
            'type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'initial_balance' => 'required|numeric|min:0',
            'is_payment_account' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            $account = Account::create($request->except('initial_balance'));

            // যদি প্রারম্ভিক ব্যালেন্স থাকে, তাহলে একটি জার্নাল এন্ট্রি তৈরি করুন
            if ($request->initial_balance > 0) {
                // প্রারম্ভিক ব্যালেন্স সাধারণত "Retained Earnings" বা "Opening Balance Equity" থেকে আসে
                $openingBalanceEquity = Account::where('code', '3020')->firstOrFail(); // Retained Earnings

                $transaction = $account->openingBalanceTransaction()->create([
                    'date' => now(),
                    'description' => 'Opening balance for ' . $account->name,
                ]);

                // Asset/Expense-এর জন্য ডেবিট, Liability/Equity/Income-এর জন্য ক্রেডিট
                if (in_array($account->type, ['Asset', 'Expense'])) {
                    // Debit the new account, Credit Equity
                    $transaction->journalEntries()->create(['account_id' => $account->id, 'debit' => $request->initial_balance]);
                    $transaction->journalEntries()->create(['account_id' => $openingBalanceEquity->id, 'credit' => $request->initial_balance]);
                } else {
                    // Credit the new account, Debit Equity
                    $transaction->journalEntries()->create(['account_id' => $account->id, 'credit' => $request->initial_balance]);
                    $transaction->journalEntries()->create(['account_id' => $openingBalanceEquity->id, 'debit' => $request->initial_balance]);
                }

                // ব্যালেন্স কলামটি এখন আর সরাসরি আপডেট হবে না, এটি গণনাকৃত হবে
            }
        });

        return redirect()->route('admin.accounts.index')->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     * একটি নির্দিষ্ট অ্যাকাউন্টের বিস্তারিত তথ্য এবং লেনদেনের তালিকা (লেজার) দেখাবে।
     */
    public function show(Request $request, Account $account)
    {
        // লেনদেনের জন্য একটি বেস কোয়েরি তৈরি করুন (JournalEntry থেকে)
        $query = $account->journalEntries()->with('transaction');

        // তারিখ অনুযায়ী ফিল্টার
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas('transaction', function ($q) use ($request) {
                $q->whereBetween('date', [$request->start_date, $request->end_date]);
            });
        }

        // ফিল্টার করা ডেটার উপর ভিত্তি করে মোট হিসাব
        $totalDebit = (clone $query)->sum('debit');
        $totalCredit = (clone $query)->sum('credit');

        $journalEntries = $query->latest()->paginate(25);

        return view('admin.accounts.show', compact('account', 'journalEntries', 'totalDebit', 'totalCredit'));
    }

    /**
     * Show the form for editing the specified resource.
     * অ্যাকাউন্ট সম্পাদনা করার ফর্ম দেখাবে।
     */
    public function edit(Account $account)
    {
        if ($account->is_system_account) {
            return redirect()->route('admin.accounts.index')->with('error', 'System accounts cannot be edited.');
        }
        return view('admin.accounts.edit', compact('account'));
    }


    /**
     * Update the specified resource in storage.
     * অ্যাকাউন্টের তথ্য আপডেট করবে।
     */
    public function update(Request $request, Account $account)
    {
        if ($account->is_system_account) {
            return redirect()->route('admin.accounts.index')->with('error', 'System accounts cannot be edited.');
        }
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('accounts')->ignore($account->id),
            ],
            'details' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        // নিরাপত্তা: ব্যালেন্স সরাসরি এডিট করার অনুমতি নেই।
        // ব্যালেন্স শুধুমাত্র লেনদেনের (Transaction) মাধ্যমে পরিবর্তিত হবে।
        $account->update($request->except('balance'));

        return redirect()->route('admin.accounts.index')->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * অ্যাকাউন্ট ডিলিট করবে (যদি কোনো লেনদেন না থাকে)।
     */
    public function destroy(Account $account)
    {
        if ($account->is_system_account) {
            return redirect()->route('admin.accounts.index')->with('error', 'System accounts cannot be deleted.');
        }
        if ($account->journalEntries()->exists()) {
            return redirect()->route('admin.accounts.index')
                ->with('error', 'Cannot delete account with journal entries.');
        }
        $account->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Account deleted successfully.');
    }
}
