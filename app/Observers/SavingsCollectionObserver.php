<?php
namespace App\Observers;
use App\Models\SavingsCollection;
class SavingsCollectionObserver
{
    public function deleting(SavingsCollection $savingsCollection): void
    {
        $savingsCollection->transactions()->delete();
    }
}
