<?php

namespace App\Http\Controllers;

use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /**
     * Get detailed information for a specific savings account.
     */

    public function getSavingsAccountDetails(SavingsAccount $savingsAccount)
    {
        // নিরাপত্তা যাচাই করা যেতে পারে

        // অ্যাকাউন্টের মালিকের তথ্য লোড করুন
        $savingsAccount->load('member');

        return response()->json($savingsAccount);
    }

    /**
     * Get detailed information for a specific loan account.
     */
    public function getLoanAccountDetails(LoanAccount $loanAccount)
    {
        // সদস্যের বিস্তারিত তথ্য এবং ছবিসহ লোড করুন
        $loanAccount->load(['member' => function($query) {
            $query->with('media');
        }]);

        // JSON রেসপন্স পাঠানোর আগে সদস্যের ছবির URL তৈরি করুন
        $memberData = $loanAccount->member->toArray();
        $memberData['photo_url'] = $loanAccount->member->getFirstMediaUrl('member_photo', 'thumb') ?: 'https://placehold.co/80x80';

        $loanData = $loanAccount->toArray();
        $loanData['member'] = $memberData; // সদস্যের তথ্য রেসপন্সে যোগ করুন

        return response()->json($loanData);
    }
}
