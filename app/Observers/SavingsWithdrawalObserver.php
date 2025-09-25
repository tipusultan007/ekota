<?php
namespace App\Observers;
use App\Models\SavingsWithdrawal;
class SavingsWithdrawalObserver
{
    public function deleting(SavingsWithdrawal $savingsWithdrawal): void
    {
        $savingsWithdrawal->transactions()->delete();
        if ($savingsWithdrawal->profitExpense) {
            $savingsWithdrawal->profitExpense->delete();
        }
    }
}
