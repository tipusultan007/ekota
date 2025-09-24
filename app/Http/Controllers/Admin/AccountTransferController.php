<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BalanceTransfer;
use App\Models\Transaction;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    protected AccountingService $accountingService;

    // কন্ট্রোলারে অ্যাকাউন্টিং সার্ভিস ইনজেক্ট করুন
    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }
    /**
     * Display a listing of recent transfers and the form to create a new one.
     */
    public function index()
    {
        $transfers = BalanceTransfer::with('fromAccount', 'toAccount')->latest()->paginate(15);
       $accounts = Account::active()->payment()->get();
        return view('admin.accounts.transfers.index', compact('transfers', 'accounts'));
    }
    public function create()
    {
       $accounts = Account::active()->payment()->get();
        return view('admin.accounts.transfers.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
        ]);

        $fromAccount = Account::findOrFail($request->from_account_id);
        $toAccount = Account::findOrFail($request->to_account_id);
        $amount = $request->amount;

        if ($fromAccount->balance < $amount) {
            return back()->with('error', 'Insufficient balance in the source account.');
        }

        DB::transaction(function () use ($request, $fromAccount, $amount) {
                // ১. ব্যালেন্স ট্রান্সফারের মূল রেকর্ড তৈরি করুন
                $transfer = BalanceTransfer::create([
                    'from_account_id' => $fromAccount->id,
                    'to_account_id' => $request->to_account_id,
                    'amount' => $amount,
                    'transfer_date' => $request->transfer_date,
                    'notes' => $request->notes,
                    'processed_by_user_id' => Auth::id(),
                ]);

                // ২. অ্যাকাউন্টিং সার্ভিস ব্যবহার করে ডাবল-এন্ট্রি লেনদেন তৈরি করুন
                $this->accountingService->createTransaction(
                    $request->transfer_date,
                    'Balance transfer from ' . $fromAccount->name . ' to ' . Account::find($request->to_account_id)->name,
                    [
                        ['account_id' => $request->to_account_id, 'debit' => $amount],   // গন্তব্য অ্যাকাউন্টে ডেবিট (সম্পদ বৃদ্ধি)
                        ['account_id' => $fromAccount->id, 'credit' => $amount], // উৎস অ্যাকাউন্ট থেকে ক্রেডিট (সম্পদ হ্রাস)
                    ],
                    $transfer
                );
            });
        return redirect()->route('admin.account-transfers.index')->with('success', 'Balance transferred successfully.');
    }

    public function edit(BalanceTransfer $account_transfer)
    {
        $accounts = Account::active()->payment()->get();
        return view('admin.accounts.transfers.edit', compact('account_transfer', 'accounts'));
    }

    public function update(Request $request, BalanceTransfer $account_transfer)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
        ]);

      
        DB::transaction(function () use ($request, $account_transfer) {
             $oldTransaction = $account_transfer->transactions()->first();
                if ($oldTransaction) {
                    $this->reverseTransaction($oldTransaction);
                }
                
                // --- ২. ব্যালেন্স ট্রান্সফারের মূল রেকর্ডটি আপডেট করুন ---
                $account_transfer->update($request->all());

                // --- ৩. নতুন অ্যাকাউন্টিং এন্ট্রি দিন ---
                $newFromAccount = Account::find($request->from_account_id);
                $newToAccount = Account::find($request->to_account_id);
                
                $this->accountingService->createTransaction(
                    $request->transfer_date,
                    'Balance transfer from ' . $newFromAccount->name . ' to ' . $newToAccount->name . ' (Updated)',
                    [
                        ['account_id' => $newToAccount->id, 'debit' => $request->amount],
                        ['account_id' => $newFromAccount->id, 'credit' => $request->amount],
                    ],
                    $account_transfer
                );
        });
        return redirect()->route('admin.account-transfers.index')->with('success', 'Transfer updated successfully.');
    }

    public function destroy(BalanceTransfer $account_transfer)
    {
        DB::transaction(function () use ($account_transfer) {
            $transaction = $account_transfer->transactions()->first();
                if ($transaction) {
                    $this->reverseTransaction($transaction);
                }
                
                // --- ২. মূল ট্রান্সফার রেকর্ডটি ডিলিট করুন ---
                $account_transfer->delete();
        });

        return redirect()->route('admin.account-transfers.index')->with('success', 'Transfer deleted and balances restored.');
    }

      private function reverseTransaction(\App\Models\Transaction $transaction)
    {
        foreach($transaction->journalEntries as $entry) {
            $account = $entry->account;
            if ($entry->debit > 0) $account->handleCredit($entry->debit);
            if ($entry->credit > 0) $account->handleDebit($entry->credit);
        }
        $transaction->journalEntries()->delete();
        $transaction->delete();
    }
}
