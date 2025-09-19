<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BalanceTransfer;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountTransferController extends Controller
{
    /**
     * Display a listing of recent transfers and the form to create a new one.
     */
    public function index()
    {
        $transfers = BalanceTransfer::with('fromAccount', 'toAccount')->latest()->paginate(15);
        $accounts = Account::where('is_active', true)->get();
        return view('admin.accounts.transfers.index', compact('transfers', 'accounts'));
    }
    public function create()
    {
        $accounts = Account::where('is_active', true)->get();
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

        DB::transaction(function () use ($request) {
            $fromAccount = Account::findOrFail($request->from_account_id);
            $toAccount = Account::findOrFail($request->to_account_id);
            $amount = $request->amount;

            $transfer = BalanceTransfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'amount' => $amount,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
                'processed_by_user_id' => Auth::id(),
            ]);

            $transfer->transactions()->create([
                'account_id' => $fromAccount->id,
                'type' => 'debit',
                'amount' => $amount,
                'description' => 'Balance transfer to ' . $toAccount->name,
                'transaction_date' => $request->transfer_date,
            ]);
            $fromAccount->decrement('balance', $amount);

            $transfer->transactions()->create([
                'account_id' => $toAccount->id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => 'Balance transfer from ' . $fromAccount->name,
                'transaction_date' => $request->transfer_date,
            ]);
            $toAccount->increment('balance', $amount);
        });
        return redirect()->route('admin.account-transfers.index')->with('success', 'Balance transferred successfully.');
    }

    public function edit(BalanceTransfer $account_transfer)
    {
        $accounts = Account::where('is_active', true)->get();
        return view('admin.accounts.transfers.edit', compact('account_transfer', 'accounts'));
    }

    public function update(Request $request, BalanceTransfer $balanceTransfer)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'amount' => 'required|numeric|min:1',
            'transfer_date' => 'required|date',
        ]);

        DB::transaction(function () use ($request, $balanceTransfer) {
            $oldFromAccount = $balanceTransfer->fromAccount;
            $oldToAccount = $balanceTransfer->toAccount;
            $oldAmount = $balanceTransfer->amount;

            $oldFromAccount->increment('balance', $oldAmount);
            $oldToAccount->decrement('balance', $oldAmount);

            $newFromAccount = Account::find($request->from_account_id);
            $newToAccount = Account::find($request->to_account_id);
            $newAmount = $request->amount;

            $newFromAccount->decrement('balance', $newAmount);
            $newToAccount->increment('balance', $newAmount);

            $balanceTransfer->update([
                'from_account_id' => $newFromAccount->id,
                'to_account_id' => $newToAccount->id,
                'amount' => $newAmount,
                'transfer_date' => $request->transfer_date,
                'notes' => $request->notes,
            ]);


            $balanceTransfer->transactions()->where('type', 'debit')->update(['account_id' => $newFromAccount->id, 'amount' => $newAmount, 'transaction_date' => $request->transfer_date]);
            $balanceTransfer->transactions()->where('type', 'credit')->update(['account_id' => $newToAccount->id, 'amount' => $newAmount, 'transaction_date' => $request->transfer_date]);
        });
        return redirect()->route('admin.account-transfers.index')->with('success', 'Transfer updated successfully.');
    }

    public function destroy(BalanceTransfer $balanceTransfer)
    {
        DB::transaction(function () use ($balanceTransfer) {
            $balanceTransfer->fromAccount->increment('balance', $balanceTransfer->amount);
            $balanceTransfer->toAccount->decrement('balance', $balanceTransfer->amount);

            $balanceTransfer->transactions()->delete();
            $balanceTransfer->delete();
        });

        return redirect()->route('admin.account-transfers.index')->with('success', 'Transfer deleted and balances restored.');
    }
}
