<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Area;
use App\Models\CapitalInvestment;
use App\Models\Expense;
use App\Models\JournalEntry;
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
    $firstEntryDate = JournalEntry::oldest('created_at')->first()->created_at ?? now();
    $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::parse($firstEntryDate);
    $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

    // ====================================================================
    // A. স্থিতিপত্র (BALANCE SHEET) - আজকের দিন পর্যন্ত মোট হিসাব
    // ====================================================================
    
    // Account::withSum() ব্যবহার করে সকল অ্যাকাউন্টের মোট ডেবিট ও ক্রেডিট আনুন
    $allAccounts = Account::withSum('journalEntries as total_debits', 'debit')
                          ->withSum('journalEntries as total_credits', 'credit')
                          ->get();
    
    // ১. সম্পদ (Assets)
    $assetAccounts = $allAccounts->where('type', 'Asset');
    $assets = [
        'cash_and_bank' => $assetAccounts->whereIn('code', ['1010', '1020', '1030'])->sum(fn($acc) => $acc->total_debits - $acc->total_credits),
        'loans_receivable' => $assetAccounts->where('code', '1110')->sum(fn($acc) => $acc->total_debits - $acc->total_credits),
    ];
    $assets['total'] = array_sum($assets);
    
    // ২. দায় (Liabilities)
    $liabilityAccounts = $allAccounts->where('type', 'Liability');
    $liabilities = [
        'members_savings' => $liabilityAccounts->where('code', '2010')->sum(fn($acc) => $acc->total_credits - $acc->total_debits),
    ];
    $liabilities['total'] = array_sum($liabilities);

    // ৩. মালিকানা সত্তা (Owner's Equity)
    $equityAccounts = $allAccounts->where('type', 'Equity');
    $equity = [
        'capital_invested' => $equityAccounts->where('code', '3010')->sum(fn($acc) => $acc->total_credits - $acc->total_debits),
        'retained_earnings' => $equityAccounts->where('code', '3020')->sum(fn($acc) => $acc->total_credits - $acc->total_debits),
    ];
    
    // ====================================================================
    // B. আয় বিবরণী (INCOME STATEMENT) - নির্বাচিত তারিখের পরিসরের জন্য
    // ====================================================================
    
    // ১. আয় (Income)
    $incomeAccounts = Account::where('type', 'Income')->pluck('id');
    $income = [
        'interest_earned' => JournalEntry::where('account_id', Account::where('code', '4010')->first()->id)
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('credit'),
        'processing_fee_income' => JournalEntry::where('account_id', Account::where('code', '4020')->first()->id)
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('credit'),
    ];
    $income['total'] = array_sum($income);

    // ২. ব্যয় (Expenses)
    $expenseAccounts = Account::where('type', 'Expense')->pluck('id');
    $expenses = [
        'salary_expenses' => JournalEntry::where('account_id', Account::where('code', '5010')->first()->id)
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('debit'),
        'profit_paid_to_members' => JournalEntry::where('account_id', Account::where('code', '5020')->first()->id)
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('debit'),
        'loan_grace_given' => JournalEntry::where('account_id', Account::where('code', '5030')->first()->id)
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('debit'),
        'operational_expenses' => JournalEntry::whereIn('account_id', Account::where('type', 'Expense')->whereNotIn('code', ['5010', '5020', '5030'])->pluck('id'))
                                ->whereBetween('created_at', [$startDate, $endDate])->sum('debit'),
    ];
    $expenses['total'] = array_sum($expenses);

    // ৩. নিট লাভ/ক্ষতি (Net Profit/Loss)
    $netProfitLoss = $income['total'] - $expenses['total'];
    
    // ৪. মালিকানা সত্তার চূড়ান্ত হিসাব
    $equity['retained_earnings_current_period'] = $netProfitLoss;
    $equity['total'] = $equity['capital_invested'] + $equity['retained_earnings'];

    return view('admin.reports.financial_summary', compact(
        'assets', 'liabilities', 'equity', 'income', 'expenses', 'netProfitLoss',
        'startDate', 'endDate'
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

     public function areaWiseReport(Request $request)
    {
        $areas = \App\Models\Area::orderBy('name')->get();
        $selectedArea = null;
        $summary = [];

        // যদি কোনো এলাকা এবং তারিখ সিলেক্ট করা হয়
        if ($request->filled('area_id')) {
            $selectedArea = \App\Models\Area::findOrFail($request->area_id);
            $memberIds = $selectedArea->members()->pluck('id');
            
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : null;
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : null;

            // --- হিসাব-নিকাশ ---
            // ১. মোট সঞ্চয় (আদায়)
            $savingsQuery = SavingsCollection::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) $savingsQuery->whereBetween('collection_date', [$startDate, $endDate]);
            $summary['total_savings_collected'] = $savingsQuery->sum('amount');
            
            // ২. মোট উত্তোলন
            $withdrawalQuery = SavingsWithdrawal::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) $withdrawalQuery->whereBetween('withdrawal_date', [$startDate, $endDate]);
            $summary['total_withdrawn'] = $withdrawalQuery->sum('total_amount');
            
            // ৩. মোট ঋণ বিতরণ
            $loanDisbursedQuery = LoanAccount::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) $loanDisbursedQuery->whereBetween('disbursement_date', [$startDate, $endDate]);
            $summary['total_loan_disbursed'] = $loanDisbursedQuery->sum('loan_amount');
            
            // ৪. মোট প্রদেয় (বিতরণ করা ঋণের উপর)
            $summary['total_payable'] = $loanDisbursedQuery->sum('total_payable');

            // ৫. মোট কিস্তি পরিশোধ
            $installmentsQuery = LoanInstallment::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) $installmentsQuery->whereBetween('payment_date', [$startDate, $endDate]);
            $summary['total_installments_paid'] = $installmentsQuery->sum('paid_amount');
            
            // ৬. মোট সুদ পরিশোধ (আনুমানিক)
            $totalPrincipalPaid = 0;
            $installmentsForInterest = $installmentsQuery->with('loanAccount')->get();
            foreach($installmentsForInterest as $inst) {
                $loan = $inst->loanAccount;
                if ($loan && $loan->total_payable > 0) {
                    $totalPrincipalPaid += $inst->paid_amount * ($loan->loan_amount / $loan->total_payable);
                }
            }
            $summary['total_interest_paid'] = $summary['total_installments_paid'] - $totalPrincipalPaid;

            // ৭. মাঠে থাকা ঋণ (Loan on Field) - এটি তারিখ পরিসরের উপর নির্ভর করে না
            $summary['loan_on_field'] = LoanAccount::whereIn('member_id', $memberIds)->where('status', 'running')->sum(DB::raw('total_payable - total_paid'));
        }

        return view('admin.reports.area_wise', compact('areas', 'selectedArea', 'summary'));
    }

     public function journalLedger(Request $request)
    {
        // এখন মূল কোয়েরি হবে Transaction মডেলের উপর
    $query = \App\Models\Transaction::with(['journalEntries.account']);

    // তারিখ অনুযায়ী ফিল্টার
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date', [$request->start_date, $request->end_date]);
    }
    
    // হিসাব (Account) অনুযায়ী ফিল্টার
    if ($request->filled('account_id')) {
        $query->whereHas('journalEntries', function ($q) use ($request) {
            $q->where('account_id', $request->account_id);
        });
    }
    
    // লেনদেনের উৎস (Transaction Type) অনুযায়ী ফিল্টার
    if ($request->filled('transaction_type')) {
        $modelClass = "App\\Models\\" . $request->transaction_type;
        if (class_exists($modelClass)) {
            $query->where('transactionable_type', $modelClass);
        }
    }

    // পেজিনেশনসহ লেনদেনের তালিকা আনুন
    $transactions = $query->latest('date')->latest('id')->paginate(15);

        // ফিল্টারের জন্য ডেটা
        $accounts = Account::orderBy('code')->get();
        // লেনদেনের উৎসগুলোর একটি তালিকা (আপনি এটি ডাইনামিকভাবেও তৈরি করতে পারেন)
        $transactionTypes = [
            'SavingsCollection' => 'Savings Collection',
            'LoanInstallment' => 'Loan Installment',
            'SavingsWithdrawal' => 'Savings Withdrawal',
            'LoanAccount' => 'Loan Disbursement',
            'CapitalInvestment' => 'Capital Investment',
            'BalanceTransfer' => 'Balance Transfer',
            'Expense' => 'Expense',
            'Salary' => 'Salary',
        ];

        return view('admin.reports.journal_ledger', compact('transactions', 'accounts', 'transactionTypes'));
    }
}
