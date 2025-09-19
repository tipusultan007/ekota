<?php
namespace App\Observers;
use App\Models\SavingsWithdrawal;
class SavingsWithdrawalObserver
{
    public function deleting(SavingsWithdrawal $savingsWithdrawal): void
    {
// এই উত্তোলনের সাথে যুক্ত সকল লেনদেন এবং খরচ ডিলিট করুন
        $savingsWithdrawal->transactions()->delete();
        if ($savingsWithdrawal->profitExpense) {
            $savingsWithdrawal->profitExpense->delete();
        }
    }
}
