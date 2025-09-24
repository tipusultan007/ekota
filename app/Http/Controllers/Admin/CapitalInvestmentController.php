<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\CapitalInvestment;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CapitalInvestmentController extends Controller
{
    use \App\Http\Controllers\Traits\TransactionReversalTrait;

    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }
    /**
     * বিনিয়োগের ফর্ম এবং সাম্প্রতিক বিনিয়োগের তালিকা দেখাবে।
     */
    public function index()
    {
        $investors = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->get();
        $accounts = Account::active()->payment()->get();
        $investments = CapitalInvestment::with('user', 'account')->latest()->paginate(15);
        return view('admin.capital_investments.index', compact('investors', 'accounts', 'investments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'investment_date' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // ১. মূল বিনিয়োগের রেকর্ড তৈরি করুন
                $investment = CapitalInvestment::create($request->all());

                // ২. অ্যাকাউন্টিং সার্ভিস ব্যবহার করে ডাবল-এন্ট্রি লেনদেন তৈরি করুন
                $depositAccount = Account::findOrFail($request->account_id);
                $capitalAccount = Account::where('code', '3010')->firstOrFail(); // Capital Investment Account (Equity)

                $this->accountingService->createTransaction(
                    $request->investment_date,
                    'Capital investment from ' . $investment->user->name,
                    [
                        ['account_id' => $depositAccount->id, 'debit' => $request->amount], // Debit Cash/Bank (Asset increases)
                        ['account_id' => $capitalAccount->id, 'credit' => $request->amount], // Credit Capital (Equity increases)
                    ],
                    $investment
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
        return redirect()->route('admin.capital_investments.index')->with('success', 'Capital investment recorded successfully.');
    }
    public function edit(CapitalInvestment $capitalInvestment)
    {
        $investors = User::whereHas('roles', fn($q) => $q->where('name', 'Admin'))->get();
        $accounts = Account::active()->payment()->get();
        return view('admin.capital_investments.edit', compact('capitalInvestment', 'investors', 'accounts'));
    }

    public function update(Request $request, CapitalInvestment $capitalInvestment)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id', // কোন অ্যাডমিন বিনিয়োগ করছেন
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:1',
            'investment_date' => 'required|date',
        ]);

        try {
            DB::transaction(function () use ($request, $capitalInvestment) {
                // ১. পুরানো অ্যাকাউন্টিং লেনদেন রিভার্স করুন
                $oldTransaction = $capitalInvestment->transaction()->first();
                if ($oldTransaction) {
                    $this->reverseTransaction($oldTransaction);
                }

                // ২. বিনিয়োগের মূল রেকর্ডটি আপডেট করুন
                $capitalInvestment->update($request->all());

                // ৩. নতুন অ্যাকাউন্টিং এন্ট্রি দিন
                $depositAccount = Account::findOrFail($request->account_id);
                $capitalAccount = Account::where('code', '3010')->firstOrFail();

                $this->accountingService->createTransaction(
                    $request->investment_date,
                    'Capital investment from ' . $capitalInvestment->user->name . ' (Updated)',
                    [
                        ['account_id' => $depositAccount->id, 'debit' => $request->amount],
                        ['account_id' => $capitalAccount->id, 'credit' => $request->amount],
                    ],
                    $capitalInvestment
                );
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage())->withInput();
        }
        return redirect()->route('admin.capital_investments.index')->with('success', 'Investment updated successfully.');
    }
    /**
     * Remove the specified resource from storage.
     * বিনিয়োগের এন্ট্রি ডিলিট করবে এবং সংশ্লিষ্ট অ্যাকাউন্টের ব্যালেন্স পুনরুদ্ধার করবে।
     */
    public function destroy(CapitalInvestment $capitalInvestment)
    {
        try {
            DB::transaction(function () use ($capitalInvestment) {
                // ১. অ্যাকাউন্টিং লেনদেন রিভার্স করুন
                $transaction = $capitalInvestment->transaction()->first();
                if ($transaction) {
                    $this->reverseTransaction($transaction);
                }

                // ২. মূল বিনিয়োগ রেকর্ডটি ডিলিট করুন
                $capitalInvestment->delete();
            });
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
        return redirect()->route('admin.capital_investments.index')->with('success', 'Investment deleted and balances restored.');
    }

    // private function reverseTransaction(\App\Models\Transaction $transaction)
    // {
    //     foreach ($transaction->journalEntries as $entry) {
    //         $account = $entry->account;
    //         if ($entry->debit > 0) $account->handleCredit($entry->debit);
    //         if ($entry->credit > 0) $account->handleDebit($entry->credit);
    //     }
    //     $transaction->journalEntries()->delete();
    //     $transaction->delete();
    // }
}
