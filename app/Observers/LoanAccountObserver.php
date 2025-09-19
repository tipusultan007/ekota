<?php

namespace App\Observers;

use App\Models\LoanAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoanAccountObserver
{
    /**
     * Handle the LoanAccount "deleting" event.
     * একটি ঋণ অ্যাকাউন্ট ডিলিট হওয়ার ঠিক আগে এই মেথডটি স্বয়ংক্রিয়ভাবে কল হবে।
     * এটি অ্যাকাউন্টের সাথে সম্পর্কিত সকল ডেটা মুছে ফেলবে।
     */
    public function deleting(LoanAccount $loanAccount): void
    {
        try {
            DB::transaction(function () use ($loanAccount) {

                // ধাপ ১: এই ঋণের সাথে যুক্ত সকল কিস্তি (Installments) ডিলিট করার জন্য লুপ চালান
                // লুপ ব্যবহার করলে প্রতিটি কিস্তির deleting ইভেন্ট (LoanInstallmentObserver) ট্রিগার হবে,
                // যা তাদের সাথে সম্পর্কিত লেনদেনগুলোও ডিলিট করে দেবে।
                foreach ($loanAccount->installments()->get() as $installment) {
                    $installment->delete();
                }

                // ধাপ ২: এই ঋণের সাথে যুক্ত জামিনদার (Guarantor) ডিলিট করুন
                // এটি GuarantorObserver-কে ট্রিগার করবে, যা জামিনদারের ডকুমেন্ট ডিলিট করবে।
                if ($loanAccount->guarantor) {
                    $loanAccount->guarantor->delete();
                }

                // ধাপ ৩: এই ঋণের সাথে সরাসরি যুক্ত লেনদেন (যেমন: বিতরণের লেনদেন) ডিলিট করুন
                $loanAccount->transactions()->delete();

                // ধাপ ৪: ঋণের সাথে সম্পর্কিত মিডিয়া (ঋণের ডকুমেন্ট) ডিলিট করুন
                $loanAccount->clearMediaCollection('loan_documents');

            });
        } catch (\Exception $e) {
            // লগ-এ এররটি রেকর্ড করুন যাতে ডিবাগ করা সহজ হয়
            Log::error("Error deleting loan account (ID: {$loanAccount->id}): " . $e->getMessage());
            // Exception থ্রো করুন যাতে মূল ডিলিট অপারেশনটি (সদস্য ডিলিট) থেমে যায়
            throw $e;
        }
    }
}
