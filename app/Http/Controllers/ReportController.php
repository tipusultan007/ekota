<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Area;
use App\Models\CapitalInvestment;
use App\Models\Expense;
use App\Models\LoanAccount;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsWithdrawal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SavingsCollection;
use App\Models\LoanInstallment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the form to select a date for the daily collection report.
     * এই মেথডটি রিপোর্ট তৈরির ফর্ম পেজটি লোড করবে।
     */
    public function dailyCollectionForm()
    {
        $collectors = [];
        // যদি ব্যবহারকারী অ্যাডমিন হন, তাহলে তিনি অন্য মাঠকর্মীদের রিপোর্টও দেখতে পারবেন
        if (Auth::user()->hasRole('Admin')) {
            $collectors = User::whereHas('roles', function ($q) {
                $q->where('name', 'Field Worker');
            })->get();
        }

        $reportDate = Carbon::today();

        return view('reports.daily_collection', compact('collectors', 'reportDate'));
    }

    /**
     * Generate and display the daily collection report based on the selected date.
     * এই মেথডটি ফর্ম থেকে ডেটা নিয়ে রিপোর্ট তৈরি করবে এবং ফলাফল দেখাবে।
     */
    public function generateDailyCollectionReport(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'collector_id' => 'nullable|exists:users,id', // অ্যাডমিনের জন্য ঐচ্ছিক ফিল্টার
        ]);

        $reportDate = Carbon::parse($request->report_date);
        $user = Auth::user();
        $collectorId = $request->collector_id;

        // সঞ্চয় আদায়ের কোয়েরি
        $savingsQuery = SavingsCollection::with('member', 'collector')
            ->whereDate('collection_date', $reportDate);

        // ঋণ কিস্তি আদায়ের কোয়েরি
        $loanQuery = LoanInstallment::with('member', 'collector')
            ->whereDate('payment_date', $reportDate);

        // ভূমিকা অনুযায়ী ফিল্টার করুন
        if ($user->hasRole('Field Worker')) {
            // মাঠকর্মী শুধুমাত্র তার নিজের রিপোর্ট দেখতে পাবে
            $savingsQuery->where('collector_id', $user->id);
            $loanQuery->where('collector_id', $user->id);
        } elseif ($user->hasRole('Admin') && $collectorId) {
            // অ্যাডমিন যদি কোনো নির্দিষ্ট মাঠকর্মীকে সিলেক্ট করেন
            $savingsQuery->where('collector_id', $collectorId);
            $loanQuery->where('collector_id', $collectorId);
        }

        $savingsCollections = $savingsQuery->get();
        $loanInstallments = $loanQuery->get();

        // মোট হিসাব
        $totalSavings = $savingsCollections->sum('amount');
        $totalLoanInstallments = $loanInstallments->sum('paid_amount');
        $grandTotal = $totalSavings + $totalLoanInstallments;

        // অ্যাডমিনের জন্য ফিল্টার অপশনগুলো আবার ভিউতে পাঠান
        $collectors = [];
        if (Auth::user()->hasRole('Admin')) {
            $collectors = User::whereHas('roles', function ($q) {
                $q->where('name', 'Field Worker');
            })->get();
        }

        // ফলাফলসহ একই ভিউতে ডেটা পাঠান
        return view('reports.daily_collection', compact(
            'savingsCollections',
            'loanInstallments',
            'totalSavings',
            'totalLoanInstallments',
            'grandTotal',
            'reportDate',
            'collectors',
            'collectorId' // সিলেক্ট করা মাঠকর্মীর আইডি মনে রাখার জন্য
        ));
    }

    /**
     * Generate and display the outstanding loan report.
     * এই মেথডটি ফিল্টার অপশনসহ বকেয়া ঋণের তালিকা দেখাবে।
     */
    public function outstandingLoanReport(Request $request)
    {
        $user = Auth::user();

        // বেস কোয়েরি
        $query = LoanAccount::with('member.area')
            ->where('status', 'running')
            ->select('*', DB::raw('total_payable - total_paid as due_amount'));

        // ভূমিকা অনুযায়ী ফিল্টার
        if ($user->hasRole('Field Worker')) {
            // সমাধান: এখানে `areas.id` নির্দিষ্ট করে দিন
            $areaIds = $user->areas()->pluck('areas.id')->toArray();

            $query->whereHas('member', function ($q) use ($areaIds) {
                $q->whereIn('area_id', $areaIds);
            });
        }

        // অ্যাডমিনের জন্য অতিরিক্ত ফিল্টার
        if ($user->hasRole('Admin')) {
            if ($request->filled('area_id')) {
                $query->whereHas('member', function ($q) use ($request) {
                    $q->where('area_id', $request->area_id);
                });
            }
            if ($request->filled('collector_id')) {
                $collector = User::find($request->collector_id);
                if ($collector) {
                    // সমাধান: এখানেও `areas.id` নির্দিষ্ট করে দিন
                    $areaIds = $collector->areas()->pluck('areas.id')->toArray();

                    $query->whereHas('member', function ($q) use ($areaIds) {
                        $q->whereIn('area_id', $areaIds);
                    });
                }
            }
        }

        $outstandingLoans = $query->orderBy('due_amount', 'desc')->paginate(25);

        // ফিল্টারের জন্য ডেটা প্রস্তুত করুন
        $areas = [];
        $collectors = [];
        if ($user->hasRole('Admin')) {
            $areas = Area::orderBy('name')->get();
            $collectors = User::whereHas('roles', fn($q) => $q->where('name', 'Field Worker'))->orderBy('name')->get();
        }

        return view('reports.outstanding_loan', compact(
            'outstandingLoans',
            'areas',
            'collectors'
        ));
    }

    /**
     * Generate a PDF statement for a specific member within a date range.
     */
    public function generateMemberStatement(Request $request, Member $member)
    {
        // নিরাপত্তা যাচাই: ব্যবহারকারীর কি এই সদস্যকে দেখার অনুমতি আছে?
        $user = Auth::user();
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('id')->toArray();
            if (!in_array($member->area_id, $areaIds)) {
                abort(403, 'UNAUTHORIZED ACTION.');
            }
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // নির্দিষ্ট তারিখের পরিসরে সঞ্চয় এবং ঋণের লেনদেনগুলো সংগ্রহ করুন
        $savings = SavingsCollection::where('member_id', $member->id)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->orderBy('collection_date', 'asc')
            ->get();

        $loans = LoanInstallment::where('member_id', $member->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();

        // দুটি কালেকশনকে একত্রিত করে তারিখ অনুযায়ী সাজান
        $transactions = collect($savings)->map(function ($item) {
            return (object) [
                'date' => $item->collection_date,
                'description' => 'Savings Deposit (' . $item->savingsAccount->account_no . ')',
                'deposit' => $item->amount,
                'withdrawal' => 0,
            ];
        })->merge(collect($loans)->map(function ($item) {
            return (object) [
                'date' => $item->payment_date,
                'description' => 'Loan Installment (' . $item->loanAccount->account_no . ')',
                'deposit' => $item->paid_amount,
                'withdrawal' => 0, // এখানে withdrawal এর পরিবর্তে deposit হিসেবে দেখানো হচ্ছে
            ];
        }))->sortBy('date');


        // PDF তৈরি করুন
        $pdf = Pdf::loadView('reports.member_statement_pdf', compact(
            'member',
            'transactions',
            'startDate',
            'endDate'
        ));

        // PDF ফাইলটি ব্রাউজারে দেখান বা ডাউনলোড করুন
        return $pdf->stream('statement-' . $member->id . '-' . time() . '.pdf');
    }

    public function dailyTransactionLog(Request $request)
    {
        $reportDate = $request->filled('report_date') ? Carbon::parse($request->report_date) : Carbon::today();
        $collectorId = $request->input('collector_id');
        $user = Auth::user();

        // কোয়েরি বিল্ডার তৈরি করুন
        $savingsQuery = SavingsCollection::with('member', 'collector')->whereDate('collection_date', $reportDate);
        $loanQuery = LoanInstallment::with('member', 'collector')->whereDate('payment_date', $reportDate);
        $withdrawalQuery = SavingsWithdrawal::with('member', 'processedBy')->whereDate('withdrawal_date', $reportDate);
        $expenseQuery = Expense::with('category', 'user')->whereDate('expense_date', $reportDate);

        $totalSavings = (clone $savingsQuery)->sum('amount');
        $totalLoans = (clone $loanQuery)->sum('paid_amount');

        // ভূমিকা এবং ফিল্টার অনুযায়ী কোয়েরি মডিফাই করুন
        if ($user->hasRole('Field Worker')) {
            $savingsQuery->where('collector_id', $user->id);
            $loanQuery->where('collector_id', $user->id);
            $withdrawalQuery->where('processed_by_user_id', $user->id);
            $expenseQuery->where('user_id', $user->id);
        } elseif ($user->hasRole('Admin') && $collectorId) {
            $savingsQuery->where('collector_id', $collectorId);
            $loanQuery->where('collector_id', $collectorId);
            $withdrawalQuery->where('processed_by_user_id', $collectorId);
            $expenseQuery->where('user_id', $collectorId);
        }

        // প্রতিটি ধরণের লেনদেনের জন্য আলাদাভাবে ডেটা সংগ্রহ করুন
        $perPage = 15; // প্রতি পেজে কয়টি আইটেম দেখাবে

        $savingsCollections = $savingsQuery->latest()->paginate($perPage, ['*'], 'savings_page');
        $loanInstallments = $loanQuery->latest()->paginate($perPage, ['*'], 'loans_page');
        $savingsWithdrawals = $withdrawalQuery->latest()->paginate($perPage, ['*'], 'withdrawals_page');
        $expenses = $expenseQuery->latest()->paginate($perPage, ['*'], 'expenses_page');

        // ফিল্টারের জন্য ডেটা
        $collectors = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Admin', 'Field Worker']))->orderBy('name')->get();

        return view('reports.daily_transaction_log_tabbed', compact(
            'savingsCollections',
            'loanInstallments',
            'savingsWithdrawals',
            'expenses',
            'reportDate',
            'collectors',
            'collectorId',
            'totalSavings',
            'totalLoans'
        ));
    }

    // app/Http-Controllers/Admin/ReportController.php

    public function financialSummary(Request $request)
    {
        // --- তারিখের পরিসর নির্ধারণ ---
        $firstTransactionDate = \App\Models\Transaction::orderBy('transaction_date', 'asc')->first()->transaction_date ?? now();
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::parse($firstTransactionDate);
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

        // ====================================================================
        // A. স্থিতিপত্র (BALANCE SHEET) - আজকের দিন পর্যন্ত মোট হিসাব
        // ====================================================================
        $totalCredits = \App\Models\Transaction::where('type', 'credit')->sum('amount');
        $totalDebits = \App\Models\Transaction::where('type', 'debit')->sum('amount');
        $calculatedCashAndBank = $totalCredits - $totalDebits;

        // ১. সম্পদ (Assets)
        $assets = [
            'cash_and_bank' => $calculatedCashAndBank,
            'loan_principal_on_field' => LoanAccount::where('status', 'running')->sum(DB::raw('total_payable - total_paid - ((total_payable - loan_amount) * (1 - (total_paid / total_payable)))')),
        ];
        $assets['total'] = $assets['cash_and_bank'] + $assets['loan_principal_on_field'];

        // ২. দায় (Liabilities)
        $liabilities = [
            'members_savings' => SavingsAccount::sum('current_balance'),
        ];
        $liabilities['total'] = $liabilities['members_savings'];

        // ৩. মালিকানা সত্তা (Owner's Equity)
        $equity = [
            'capital_invested' => CapitalInvestment::sum('amount'),
            // 'retained_earnings' => ... (এটি আয় বিবরণী থেকে আসবে)
        ];

        // ====================================================================
        // B. আয় বিবরণী (INCOME STATEMENT) - নির্বাচিত তারিখের পরিসরের জন্য
        // ====================================================================

        // ১. আয় (Income)
        $totalInstallments = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])->sum('paid_amount');
        $totalPrincipalCollected = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])
            ->get()->sum(function ($inst) {
                $loan = $inst->loanAccount;
                return ($loan && $loan->total_payable > 0) ? ($inst->paid_amount * ($loan->loan_amount / $loan->total_payable)) : 0;
            });
        $income = [
            'interest_earned' => $totalInstallments - $totalPrincipalCollected,
            // ভবিষ্যতে অন্যান্য আয় এখানে যোগ হবে
        ];
        $income['total'] = $income['interest_earned'];

        // ২. ব্যয় (Expenses)
        $expenses = [
            'profit_paid_to_members' => SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('profit_amount'),
            'loan_grace_given' => LoanAccount::where('status', 'paid')->whereBetween('updated_at', [$startDate, $endDate])->sum('grace_amount'),
            'operational_expenses' => Expense::whereHas('category', fn($q) => $q->where('name', '!=', 'Employee Salary'))
                ->whereBetween('expense_date', [$startDate, $endDate])->sum('amount'),
            'salary_expenses' => Expense::whereHas('category', fn($q) => $q->where('name', 'Employee Salary'))
                ->whereBetween('expense_date', [$startDate, $endDate])->sum('amount'),
        ];
        $expenses['total'] = array_sum($expenses);

        // ৩. নিট লাভ/ক্ষতি (Net Profit/Loss)
        $netProfitLoss = $income['total'] - $expenses['total'];

        // মালিকানা সত্তার Retained Earnings (অর্জিত মুনাফা) গণনা
        // এটি একটি সরলীকৃত হিসাব, পূর্ণাঙ্গ সিস্টেমের জন্য একটি আলাদা টেবিল লাগবে
        $equity['retained_earnings'] = $netProfitLoss;
        $equity['total'] = $equity['capital_invested'] + $equity['retained_earnings'];

        return view('admin.reports.financial_summary', compact(
            'assets',
            'liabilities',
            'equity',
            'income',
            'expenses',
            'netProfitLoss',
            'startDate',
            'endDate'
        ));
    }


    /**
     * Helper function to calculate summaries within a date range.
     */
    private function calculateDateRangeSummary($startDate, $endDate)
    {
        // যদি কোনো তারিখ না দেওয়া থাকে, তাহলে সকল হিসাব শূন্য দেখাবে
        if (!$startDate || !$endDate) {
            return [
                'capitalInvestedInRange' => 0,
                'savingsCollected' => 0,
                'loanInstallmentsCollected' => 0,
                'savingsWithdrawn' => 0,
                'profitGiven' => 0,
                'interestGained' => 0,
                'totalExpense' => 0
            ];
        }

        $capitalInvestedInRange = CapitalInvestment::whereBetween('investment_date', [$startDate, $endDate])->sum('amount');

        // নির্দিষ্ট পরিসরে মোট সঞ্চয় জমা
        $savingsCollected = SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('amount');

        // নির্দিষ্ট পরিসরে মোট ঋণের কিস্তি জমা
        $loanInstallmentsCollected = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])->sum('paid_amount');

        // নির্দিষ্ট পরিসরে মোট সঞ্চয় উত্তোলন (আসল + মুনাফা)
        $savingsWithdrawn = SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('total_amount');

        // নির্দিষ্ট পরিসরে মোট মুনাফা প্রদান (উত্তোলনের সময়)
        $profitGiven = SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('profit_amount');

        // নির্দিষ্ট পরিসরে অর্জিত সুদ (ঋণ থেকে)
        // এটি একটি জটিল হিসাব। সরলীকরণের জন্য, আমরা ধরে নিচ্ছি প্রতিটি কিস্তির একটি অংশ সুদ।
        // একটি ভালো উপায় হলো প্রতিটি কিস্তির সাথে কতটুকু সুদ জমা হলো তা রেকর্ড করা।
        // আপাতত, আমরা মোট কিস্তির একটি আনুমানিক শতাংশকে সুদ হিসেবে ধরছি।
        $totalLoanPrincipalCollected = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])
            ->get()->sum(function ($installment) {
                // এখানে একটি সরলীকৃত ধারণা ব্যবহার করা হচ্ছে
                $loan = $installment->loanAccount;
                if ($loan && $loan->total_payable > 0) {
                    $principalPortion = $installment->paid_amount * ($loan->loan_amount / $loan->total_payable);
                    return $principalPortion;
                }
                return $installment->paid_amount;
            });
        $interestGained = $loanInstallmentsCollected - $totalLoanPrincipalCollected;

        // নির্দিষ্ট পরিসরে মোট খরচ
        $totalExpense = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');

        return compact('capitalInvestedInRange', 'savingsCollected', 'loanInstallmentsCollected', 'savingsWithdrawn', 'profitGiven', 'interestGained', 'totalExpense');
    }
}
