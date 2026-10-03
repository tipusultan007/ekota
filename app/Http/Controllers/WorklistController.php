<?php

namespace App\Http\Controllers;

use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            ->whereIn('member_id', fn ($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->where(function ($q) use ($todayString) {
                $q->whereDate('next_due_date', '<=', $todayString)
                  ->orWhereHas('collections', function ($sub) use ($todayString) {
                      $sub->whereDate('collection_date', $todayString);
                  });
            })
            ->with(['member', 'collections' => function ($query) use ($todayString) {
                $query->whereDate('collection_date', $todayString);
            }])
            ->orderByRaw('CAST(account_no AS UNSIGNED) ASC')
            ->get();

        // --- আজকের ঋণ কিস্তি আদায়ের তালিকা (নতুন এবং সঠিক কোয়েরি) ---
        $loanInstallmentsDueToday = LoanAccount::whereIn('member_id', fn ($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->where(function ($q) use ($todayString) {
                $q->where(function ($pending) use ($todayString) {
                    $pending->where('status', 'running')
                            ->whereDate('next_due_date', '<=', $todayString)
                            ->whereRaw('total_paid + grace_amount < total_payable');
                })
                ->orWhereHas('installments', function ($sub) use ($todayString) {
                    $sub->whereDate('payment_date', $todayString);
                });
            })
            ->with(['member', 'installments' => function ($query) use ($todayString) {
                $query->whereDate('payment_date', $todayString);
            }])
            ->orderByRaw('CAST(account_no AS UNSIGNED) ASC')
            ->get();

        $savingsTarget = 0;
        $loanTarget = $loanInstallmentsDueToday->sum('installment_amount');
        $totalTarget = $savingsTarget + $loanTarget;

        // ডিফল্ট ক্যাশ অ্যাকাউন্ট (ID) সংগ্রহ
        $defaultCashAccount = \App\Models\Account::where('code', '1010')->first();
        $defaultCashAccountId = $defaultCashAccount ? $defaultCashAccount->id : null;

        return view('worklist.today', compact(
            'savingsDueToday',
            'loanInstallmentsDueToday',
            'totalTarget',
            'defaultCashAccountId'
        ));
    }
}
