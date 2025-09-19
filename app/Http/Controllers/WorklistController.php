<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use Carbon\Carbon;

class WorklistController extends Controller
{
    /**
     * Display the daily worklist for the authenticated field worker.
     */
    public function today()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $todayString = $today->toDateString(); // Y-m-d ফরম্যাটে আজকের তারিখ

        // ব্যবহারকারীর নির্ধারিত এলাকার আইডিগুলো নিন
        if ($user->hasRole('Admin')) {
            $areaIds = \App\Models\Area::pluck('id')->toArray();
        } else {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
        }

        // --- আজকের সঞ্চয় আদায়ের তালিকা (নতুন এবং সঠিক কোয়েরি) ---
        $savingsDueToday = SavingsAccount::where('status', 'active')
            ->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->whereDate('next_due_date', '<=', $todayString) // যাদের কিস্তি আজ বা তার আগে বকেয়া
            ->whereDoesntHave('collections', function ($query) use ($todayString) {
                // এবং যাদের জন্য আজকের তারিখে কোনো কালেকশন এন্ট্রি নেই
                $query->whereDate('collection_date', '=', $todayString);
            })
            ->with('member')
            ->orderBy('next_due_date', 'asc') // সবচেয়ে পুরানো বকেয়া আগে দেখাবে
            ->get();


        // --- আজকের ঋণ কিস্তি আদায়ের তালিকা (নতুন এবং সঠিক কোয়েরি) ---
        $loanInstallmentsDueToday = LoanAccount::where('status', 'running')
            ->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->whereDate('next_due_date', '<=', $todayString) // যাদের কিস্তি আজ বা তার আগে বкеয়া
            ->whereDoesntHave('installments', function ($query) use ($todayString) {
                // এবং যাদের জন্য আজকের তারিখে কোনো কিস্তির এন্ট্রি নেই
                $query->whereDate('payment_date', '=', $todayString);
            })
            ->with('member')
            ->orderBy('next_due_date', 'asc')
            ->get();

        // --- আজকের কালেকশন টার্গেট (সঠিক গণনা) ---
        // এই লজিকটি আপনার সমিতির নিয়ম অনুযায়ী আরও জটিল হতে পারে।
        // যেমন, দৈনিক স্কিমের জন্য একটি নির্দিষ্ট পরিমাণ ধরা যেতে পারে।
        // আপাতত, আমরা ঋণের কিস্তির পরিমাণকে টার্গেট হিসেবে ধরছি।
        $savingsTarget = 0; // সঞ্চয়ের টার্গেট নির্ধারণ করা জটিল, কারণ এটি পরিবর্তনশীল হতে পারে
        $loanTarget = $loanInstallmentsDueToday->sum('installment_amount');
        $totalTarget = $savingsTarget + $loanTarget;

        // ভিউ ফাইলের নাম পরিবর্তন করা হলো (ঐচ্ছিক, কিন্তু সামঞ্জস্যের জন্য ভালো)
        return view('worklist.today', compact(
            'savingsDueToday',
            'loanInstallmentsDueToday',
            'totalTarget'
        ));
    }
}
