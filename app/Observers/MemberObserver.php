<?php
namespace App\Observers;
use App\Models\Member;
class MemberObserver
{
    public function deleting(Member $member): void
    {
        foreach ($member->loanAccounts()->get() as $loanAccount) {
            $loanAccount->delete();
        }

        foreach ($member->savingsAccounts()->get() as $savingsAccount) {
            $savingsAccount->delete();
        }

        $member->clearMediaCollection('member_photo');
        $member->clearMediaCollection('member_signature');
    }
}
