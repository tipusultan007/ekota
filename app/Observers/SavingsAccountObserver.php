<?php
namespace App\Observers;
use App\Models\SavingsAccount;
use Illuminate\Support\Facades\DB;

class SavingsAccountObserver
{
    public function deleting(SavingsAccount $savingsAccount): void
    {
        try {
            DB::transaction(function () use ($savingsAccount) {

                // ধাপ ১: এই অ্যাকাউন্টের সকল সঞ্চয় আদায় (Collections) ডিলিট করার জন্য লুপ চালান
                // লুপ ব্যবহার করলে প্রতিটি কালেকশনের deleting ইভেন্ট (SavingsCollectionObserver) ট্রিগার হবে,
                // যা তাদের সাথে সম্পর্কিত লেনদেনগুলোও ডিলিট করে দেবে।
                foreach ($savingsAccount->collections()->get() as $collection) {
                    $collection->delete();
                }

                // ধাপ ২: এই অ্যাকাউন্টের সকল সঞ্চয় উত্তোলন (Withdrawals) ডিলিট করার জন্য লুপ চালান
                // এটি SavingsWithdrawalObserver-কে ট্রিগার করবে।
                foreach ($savingsAccount->withdrawals()->get() as $withdrawal) {
                    $withdrawal->delete();
                }

                // ধাপ ৩: অ্যাকাউন্টের সাথে সম্পর্কিত মিডিয়া (নমিনির ছবি) ডিলিট করুন
                $savingsAccount->clearMediaCollection('nominee_photo');

            });
        } catch (\Exception $e) {
            // লগ-এ এররটি রেকর্ড করুন যাতে ডিবাগ করা সহজ হয়
            \Log::error("Error deleting savings account (ID: {$savingsAccount->id}): " . $e->getMessage());
            // প্রয়োজনে, আপনি এখানে একটি Exception থ্রো করতে পারেন যাতে মূল ডিলিট অপারেশনটি থেমে যায়
            // throw $e;
        }
    }
}
