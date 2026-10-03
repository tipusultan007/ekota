<?php

namespace App\Observers;

use App\Models\Transaction;

class TransactionObserver
{
    /**
     * Handle the Transaction "deleting" event.
     */
    public function deleting(Transaction $transaction): void
    {
        $transaction->journalEntries()->delete();
    }
}
