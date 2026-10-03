<?php
namespace App\Observers;
use App\Models\SavingsCollection;
class SavingsCollectionObserver
{
    public function deleting(SavingsCollection $savingsCollection): void
    {
        foreach ($savingsCollection->transactions as $transaction) {
            $transaction->delete();
        }
    }
}
