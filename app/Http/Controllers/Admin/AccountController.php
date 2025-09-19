<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     * সকল আর্থিক অ্যাকাউন্টের তালিকা দেখাবে।
     */
    public function index()
    {
        $accounts = Account::latest()
            ->withSum(['transactions as total_credits' => function ($query) {
                $query->where('type', 'credit');
            }], 'amount')
            ->withSum(['transactions as total_debits' => function ($query) {
                $query->where('type', 'debit');
            }], 'amount')
            ->paginate(15);

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
            'name' => 'required|string|max:255|unique:accounts,name',
            'details' => 'nullable|string',
        ]);

        Account::create($request->all());

        return redirect()->route('admin.accounts.index')->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified resource.
     * একটি নির্দিষ্ট অ্যাকাউন্টের বিস্তারিত তথ্য এবং লেনদেনের তালিকা (লেজার) দেখাবে।
     */
    public function show(Request $request, Account $account)
    {
        // লেনদেনের জন্য একটি বেস কোয়েরি তৈরি করুন
        $query = $account->transactions();

        // তারিখ অনুযায়ী ফিল্টার প্রয়োগ করুন
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transaction_date', [$request->start_date, $request->end_date]);
        }

        // ফিল্টার করা ডেটার উপর ভিত্তি করে মোট হিসাব করুন
        $totalCredit = (clone $query)->where('type', 'credit')->sum('amount');
        $totalDebit = (clone $query)->where('type', 'debit')->sum('amount');

        // পেজিনেশনসহ লেনদেনের তালিকা আনুন
        $transactions = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(25);

        return view('admin.accounts.show', compact(
            'account',
            'transactions',
            'totalCredit',
            'totalDebit'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     * অ্যাকাউন্ট সম্পাদনা করার ফর্ম দেখাবে।
     */
    public function edit(Account $account)
    {
        return view('admin.accounts.edit', compact('account'));
    }

    /**
     * Update the specified resource in storage.
     * অ্যাকাউন্টের তথ্য আপডেট করবে।
     */
    public function update(Request $request, Account $account)
    {
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
        // যদি অ্যাকাউন্টে কোনো লেনদেন থাকে, তাহলে ডিলিট করা যাবে না।
        if ($account->transactions()->exists()) {
            return redirect()->route('admin.accounts.index')
                ->with('error', 'Cannot delete this account because it has existing transactions. Please delete the transactions first or make the account inactive.');
        }

        // ক্যাশ বা ডিফল্ট অ্যাকাউন্ট ডিলিট করা থেকে বিরত রাখুন (ঐচ্ছিক)
        if (in_array($account->id, [1, 2])) { // ধরে নেওয়া হচ্ছে আইডি 1 এবং 2 ডিফল্ট
            return redirect()->route('admin.accounts.index')->with('error', 'Cannot delete default system accounts.');
        }

        $account->delete();

        return redirect()->route('admin.accounts.index')->with('success', 'Account deleted successfully.');
    }
}
