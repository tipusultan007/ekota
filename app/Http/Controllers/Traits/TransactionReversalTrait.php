<?php

namespace App\Http\Controllers\Traits;

use App\Models\Transaction;

trait TransactionReversalTrait
{
    /**
     * Reverses a transaction by reversing its journal entries and updating account balances.
     * Then deletes the transaction and its journal entries.
     *
     * @param \App\Models\Transaction $transaction
     * @return void
     */
    protected function reverseTransaction(Transaction $transaction): void
    {
        if (!$transaction) {
            return;
        }

        $transaction->journalEntries()->delete();
        $transaction->delete();
    }
}