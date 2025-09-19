<?php
namespace App\Observers;
use App\Models\SavingsCollection;
class SavingsCollectionObserver
{
    public function deleting(SavingsCollection $savingsCollection): void
    {
        // এই কালেকশনের সাথে যুক্ত সকল লেনদেন ডিলিট করুন
        $savingsCollection->transactions()->delete();
    }
}
