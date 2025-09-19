<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\SavingsWithdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\SavingsCollection;
use App\Models\LoanInstallment;
use App\Models\Area;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            return $this->adminDashboard();
        } elseif ($user->hasRole('Field Worker')) {
            return $this->fieldWorkerDashboard();
        }

        // অন্য কোনো ভূমিকা থাকলে সাধারণ ড্যাশবোর্ড
        return view('dashboard');
    }

    /**
     * অ্যাডমিনের জন্য ড্যাশবোর্ডের ডেটা প্রস্তুত ও প্রদর্শন
     */
    private function adminDashboard()
    {
        // === স্ট্যাটাস কার্ড ===
        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();
        $totalSavings = SavingsAccount::sum('current_balance');
        $totalLoanDisbursed = LoanAccount::sum('loan_amount');
        $totalLoanDue = LoanAccount::where('status', 'running')->sum(DB::raw('total_payable - total_paid'));

        $withdrawableAmount = SavingsAccount::where('status', 'active')->sum('current_balance'); // মোট সঞ্চয় যা তোলা সম্ভব
        $totalWithdrawn = SavingsWithdrawal::sum('total_amount'); // সর্বমোট উত্তোলন

        // === আজকের সারাংশ ===
        $todaySavings = SavingsCollection::whereDate('collection_date', today())->sum('amount');
        $todayInstallments = LoanInstallment::whereDate('payment_date', today())->sum('paid_amount');

        $todayWithdrawals = SavingsWithdrawal::whereDate('withdrawal_date', today())->sum('total_amount');
        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        // === চার্টের ডেটা: গত ৬ মাসের কালেকশন ===
        $monthlyCollections = $this->getMonthlyCollectionData();

        // === পাই চার্টের ডেটা: এলাকাভিত্তিক সদস্য ===
        $areaWiseMembers = Area::withCount('members')
            ->get()
            ->map(function ($area) {
                return [
                    'name' => $area->name,
                    'count' => $area->members_count,
                ];
            });

        return view('dashboard.admin', compact(
            'totalMembers', 'activeMembers', 'totalSavings', 'totalLoanDisbursed', 'totalLoanDue',
            'todaySavings', 'todayInstallments', 'monthlyCollections', 'areaWiseMembers','todayExpenses', 'todayWithdrawals', 'withdrawableAmount',
            'totalWithdrawn',
        ));
    }

    /**
     * মাঠকর্মীর জন্য ড্যাশবোর্ডের ডেটা প্রস্তুত ও প্রদর্শন
     */
    private function fieldWorkerDashboard()
    {
        $user = Auth::user();
        $areaIds = $user->areas()->pluck('areas.id')->toArray();
        $today = Carbon::today();
        $todayString = $today->toDateString();

        // === স্ট্যাটাস কার্ড (অপরিবর্তিত) ===
        $totalMembers = Member::whereIn('area_id', $areaIds)->count();
        $totalSavings = SavingsAccount::whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))->sum('current_balance');
        $totalLoanDue = LoanAccount::where('status', 'running')->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))->sum(DB::raw('total_payable - total_paid'));

        // === আজকের পারফরম্যান্স (অপরিবর্তিত) ===
        $todaySavings = SavingsCollection::where('collector_id', $user->id)->whereDate('collection_date', today())->sum('amount');
        $todayInstallments = LoanInstallment::where('collector_id', $user->id)->whereDate('payment_date', today())->sum('paid_amount');

        // === সেরা ৫ খেলাপি (অপরিবর্তিত) ===
        $topDefaulters = LoanAccount::where('status', 'running')->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))->select('*', DB::raw('total_payable - total_paid as due_amount'))->orderBy('due_amount', 'desc')->with('member')->limit(5)->get();

        // ১. সঞ্চয় আদায়ের তালিকা
        $savingsDueToday = SavingsAccount::where('status', 'active')
            ->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->whereDate('next_due_date', '<=', $todayString) // যাদের কিস্তি আজ বা তার আগে বকেয়া
            ->whereDoesntHave('collections', function ($query) use ($todayString) {
                // এবং যাদের জন্য আজকের তারিখে কোনো কালেকশন এন্ট্রি নেই
                $query->whereDate('collection_date', '=', $todayString);
            })
            ->with('member')
            ->orderBy('next_due_date', 'asc')
            ->get();

        // ২. ঋণ কিস্তি আদায়ের তালিকা
        $loanInstallmentsDueToday = LoanAccount::where('status', 'running')
            ->whereIn('member_id', fn($q) => $q->select('id')->from('members')->whereIn('area_id', $areaIds))
            ->whereDate('next_due_date', '<=', $todayString) // যাদের কিস্তি আজ বা তার আগে বকেয়া
            ->whereDoesntHave('installments', function ($query) use ($todayString) {
                // এবং যাদের জন্য আজকের তারিখে কোনো কিস্তির এন্ট্রি নেই
                $query->whereDate('payment_date', '=', $todayString);
            })
            ->with('member')
            ->orderBy('next_due_date', 'asc')
            ->get();
        // ---------------------------------------------


        return view('dashboard.field_worker', compact(
            'totalMembers', 'totalSavings', 'totalLoanDue',
            'todaySavings', 'todayInstallments', 'topDefaulters',
            'savingsDueToday', 'loanInstallmentsDueToday' // নতুন ভেরিয়েবল পাস করুন
        ));
    }

    /**
     * হেল্পার ফাংশন: মাসিক কালেকশনের ডেটা তৈরি করে
     */
    private function getMonthlyCollectionData()
    {
        $months = [];
        $savingsData = [];
        $loanData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $date->format('M');
            $months[] = $monthName;

            $savingsData[] = SavingsCollection::whereYear('collection_date', $date->year)
                ->whereMonth('collection_date', $date->month)
                ->sum('amount');

            $loanData[] = LoanInstallment::whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('paid_amount');
        }

        return [
            'months' => $months,
            'savings' => $savingsData,
            'loans' => $loanData,
        ];
    }
}
