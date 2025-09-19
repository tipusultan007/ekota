<?php
namespace App\Observers;
use App\Models\Member;
class MemberObserver
{
    public function deleting(Member $member): void
    {
// সদস্যের সকল ঋণ অ্যাকাউন্ট ডিলিট করার জন্য লুপ চালান
// (এটি LoanAccountObserver-কে ট্রিগার করবে)
        foreach ($member->loanAccounts()->get() as $loanAccount) {
            $loanAccount->delete();
        }

// সদস্যের সকল সঞ্চয় অ্যাকাউন্ট ডিলিট করার জন্য লুপ চালান
// (এটি SavingsAccountObserver-কে ট্রিগার করবে)
        foreach ($member->savingsAccounts()->get() as $savingsAccount) {
            $savingsAccount->delete();
        }

// সদস্যের মিডিয়া ফাইল ডিলিট করুন
        $member->clearMediaCollection('member_photo');
        $member->clearMediaCollection('member_signature');
    }
}
