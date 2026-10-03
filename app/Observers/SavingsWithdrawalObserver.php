<?php

namespace App\Observers;

use App\Models\SavingsWithdrawal;

class SavingsWithdrawalObserver
{
    public function deleting(SavingsWithdrawal $savingsWithdrawal): void
    {
        foreach ($savingsWithdrawal->transactions as $transaction) {
            $transaction->delete();
        }

        if ($savingsWithdrawal->profitExpense) {
            $savingsWithdrawal->profitExpense->delete();
        }
    }
}
