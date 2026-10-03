<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Area;
use App\Models\CapitalInvestment;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Models\LoanAccount;
use App\Models\LoanInstallment;
use App\Models\Member;
use App\Models\SavingsAccount;
use App\Models\SavingsCollection;
use App\Models\SavingsWithdrawal;
use App\Models\User;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $collectors = User::whereHas('roles', fn ($q) => $q->where('name', 'Field Worker'))->orderBy('name')->get();
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
            if (! in_array($member->area_id, $areaIds)) {
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
                'description' => 'Savings Deposit ('.$item->savingsAccount->account_no.')',
                'deposit' => $item->amount,
                'withdrawal' => 0,
            ];
        })->merge(collect($loans)->map(function ($item) {
            return (object) [
                'date' => $item->payment_date,
                'description' => 'Loan Installment ('.$item->loanAccount->account_no.')',
                'deposit' => $item->paid_amount,
                'withdrawal' => 0, // এখানে withdrawal এর পরিবর্তে deposit হিসেবে দেখানো হচ্ছে
            ];
        }))->sortBy('date');

        // mPDF ব্যবহার করে PDF তৈরি করুন (শেয়ার্ড হোস্টিং এ কাজ করার জন্য)
        $pdf = PDF::loadView('reports.member_statement_pdf', compact(
            'member',
            'transactions',
            'startDate',
            'endDate'
        ));

        $filename = $member->account_no . '_statement_' . $startDate->format('d-m-Y') . '_' . $endDate->format('d-m-Y') . '.pdf';
        
        return $pdf->download($filename);
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
        $incomeQuery = \App\Models\Income::with('category', 'user')->whereDate('income_date', $reportDate);

        $totalSavings = (clone $savingsQuery)->sum('amount');
        $totalLoans = (clone $loanQuery)->sum('paid_amount');
        $totalIncomes = (clone $incomeQuery)->sum('amount');

        // ভূমিকা এবং ফিল্টার অনুযায়ী কোয়েরি মডিফাই করুন
        if ($user->hasRole('Field Worker')) {
            $savingsQuery->where('collector_id', $user->id);
            $loanQuery->where('collector_id', $user->id);
            $withdrawalQuery->where('processed_by_user_id', $user->id);
            $expenseQuery->where('user_id', $user->id);
            $incomeQuery->where('user_id', $user->id);
        } elseif ($user->hasRole('Admin') && $collectorId) {
            $savingsQuery->where('collector_id', $collectorId);
            $loanQuery->where('collector_id', $collectorId);
            $withdrawalQuery->where('processed_by_user_id', $collectorId);
            $expenseQuery->where('user_id', $collectorId);
            $incomeQuery->where('user_id', $collectorId);
        }

        // প্রতিটি ধরণের লেনদেনের জন্য আলাদাভাবে ডেটা সংগ্রহ করুন
        $perPage = 15; // প্রতি পেজে কয়টি আইটেম দেখাবে

        $savingsCollections = $savingsQuery->latest()->paginate($perPage, ['*'], 'savings_page');
        $loanInstallments = $loanQuery->latest()->paginate($perPage, ['*'], 'loans_page');
        $savingsWithdrawals = $withdrawalQuery->latest()->paginate($perPage, ['*'], 'withdrawals_page');
        $expenses = $expenseQuery->latest()->paginate($perPage, ['*'], 'expenses_page');
        $incomes = $incomeQuery->latest()->paginate($perPage, ['*'], 'incomes_page');

        // ফিল্টারের জন্য ডেটা
        $collectors = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Admin', 'Field Worker']))->orderBy('name')->get();

        return view('reports.daily_transaction_log_tabbed', compact(
            'savingsCollections',
            'loanInstallments',
            'savingsWithdrawals',
            'expenses',
            'incomes',
            'reportDate',
            'collectors',
            'collectorId',
            'totalSavings',
            'totalLoans',
            'totalIncomes'
        ));
    }

    // app/Http-Controllers/Admin/ReportController.php

    public function financialSummary(Request $request)
    {
        // Default to current year or all time if possible. Here we default to start of year.
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfYear();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

        // 1. Savings Section
        $totalSavingsCollected = SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('amount');
        $totalSavingsInterestGiven = SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('interest_amount')
                                     + SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('profit_amount');
        $totalSavingsWithdrawn = SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('total_amount')
                                 + SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('withdraw_amount');

        $currentSavingsLiability = SavingsAccount::sum('current_balance');

        // 2. Loan Section
        $totalLoansDisbursed = LoanAccount::whereBetween('disbursement_date', [$startDate, $endDate])->sum('loan_amount');

        // Interest Gained (Account code 4010)
        $interestAccount = Account::where('code', '4010')->first();
        $totalInterestGained = $interestAccount ? JournalEntry::where('account_id', $interestAccount->id)
            ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->sum('credit') : 0;

        // Loan Fees (Account code 4020)
        $feeAccount = Account::where('code', '4020')->first();
        $totalLoanFees = $feeAccount ? JournalEntry::where('account_id', $feeAccount->id)
            ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->sum('credit') : 0;

        $totalLoanPaidInPeriod = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])->sum('paid_amount');

        $loanOutstanding = LoanAccount::where('status', 'running')->sum(DB::raw('total_payable - total_paid'));

        // 3. Expense Section
        // Salary (Account code 5010)
        $salaryAccount = Account::where('code', '5010')->first();
        $totalSalaryExpense = $salaryAccount ? JournalEntry::where('account_id', $salaryAccount->id)
            ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->sum('debit') : 0;

        // Other Operational Expenses
        $otherExpenseAccountIds = Account::where('type', 'Expense')->whereNotIn('code', ['5010', '5020', '5030'])->pluck('id');
        $totalOtherExpense = JournalEntry::whereIn('account_id', $otherExpenseAccountIds)
            ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->sum('debit');

        // Other Income (All other income accounts)
        $otherIncomeAccountIds = Account::where('type', 'Income')
            ->whereNotIn('code', ['4010', '4020'])
            ->pluck('id');
        $totalOtherIncome = JournalEntry::whereIn('account_id', $otherIncomeAccountIds)
            ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
            ->sum('credit');

        // 4. Financial Position (Snapshot - always current)
        $cashBalance = Account::payment()->active()->get()->sum(fn ($acc) => $acc->balance);
        $totalAssets = $cashBalance + $loanOutstanding;

        // 5. Profit/Loss Calculation
        $totalIncome = $totalInterestGained + $totalLoanFees + $totalOtherIncome;
        $totalCosts = $totalSalaryExpense + $totalOtherExpense + $totalSavingsInterestGiven;
        $netProfitLoss = $totalIncome - $totalCosts;

        return view('admin.reports.financial_summary', compact(
            'totalSavingsCollected', 'totalSavingsInterestGiven', 'totalSavingsWithdrawn', 'currentSavingsLiability',
            'totalLoansDisbursed', 'totalInterestGained', 'totalLoanFees', 'totalOtherIncome', 'totalLoanPaidInPeriod', 'loanOutstanding',
            'totalSalaryExpense', 'totalOtherExpense', 'cashBalance', 'totalAssets', 'netProfitLoss',
            'startDate', 'endDate'
        ));
    }

    /**
     * Helper function to calculate summaries within a date range.
     */
    private function calculateDateRangeSummary($startDate, $endDate)
    {
        // যদি কোনো তারিখ না দেওয়া থাকে, তাহলে সকল হিসাব শূন্য দেখাবে
        if (! $startDate || ! $endDate) {
            return [
                'capitalInvestedInRange' => 0,
                'savingsCollected' => 0,
                'loanInstallmentsCollected' => 0,
                'savingsWithdrawn' => 0,
                'profitGiven' => 0,
                'interestGained' => 0,
                'totalExpense' => 0,
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
            if ($startDate && $endDate) {
                $savingsQuery->whereBetween('collection_date', [$startDate, $endDate]);
            }
            $summary['total_savings_collected'] = $savingsQuery->sum('amount');

            // ২. মোট উত্তোলন
            $withdrawalQuery = SavingsCollection::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) {
                $withdrawalQuery->whereBetween('collection_date', [$startDate, $endDate]);
            }
            $summary['total_withdrawn'] = $withdrawalQuery->sum('withdraw_amount');
            
            // যদি SavingsWithdrawal মডেলও ব্যবহার করা হয়, তবে সেটিও যোগ করা যেতে পারে। 
            // তবে ব্যবহারকারীর অনুরোধ অনুযায়ী SavingsCollection ব্যবহার করা হলো।
            // $summary['total_withdrawn'] += SavingsWithdrawal::whereIn('member_id', $memberIds)
            //     ->when($startDate && $endDate, fn($q) => $q->whereBetween('withdrawal_date', [$startDate, $endDate]))
            //     ->sum('total_amount');

            // ৩. মোট ঋণ বিতরণ
            $loanDisbursedQuery = LoanAccount::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) {
                $loanDisbursedQuery->whereBetween('disbursement_date', [$startDate, $endDate]);
            }
            $summary['total_loan_disbursed'] = $loanDisbursedQuery->sum('loan_amount');

            // ৪. মোট প্রদেয় (বিতরণ করা ঋণের উপর)
            $summary['total_payable'] = $loanDisbursedQuery->sum('total_payable');

            // ৫. মোট কিস্তি পরিশোধ
            $installmentsQuery = LoanInstallment::whereIn('member_id', $memberIds);
            if ($startDate && $endDate) {
                $installmentsQuery->whereBetween('payment_date', [$startDate, $endDate]);
            }
            $summary['total_installments_paid'] = $installmentsQuery->sum('paid_amount');

            // ৬. মোট সুদ পরিশোধ (আনুমানিক)
            $totalPrincipalPaid = 0;
            $installmentsForInterest = $installmentsQuery->with('loanAccount')->get();
            foreach ($installmentsForInterest as $inst) {
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
            $modelClass = 'App\\Models\\'.$request->transaction_type;
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

    public function cashbook(Request $request)
    {
        // --- ফিল্টার প্যারামিটার প্রস্তুত করুন ---
        $accountId = $request->input('account_id'); // 'all' অথবা একটি নির্দিষ্ট আইডি হতে পারে
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

        // ফিল্টারের জন্য সকল পেমেন্ট অ্যাকাউন্টের তালিকা
        $accounts = Account::active()->payment()->orderBy('name')->get();

        // --- কোন কোন অ্যাকাউন্টের উপর কোয়েরি চলবে তা নির্ধারণ করুন ---
        $accountIdsToQuery = [];
        if ($accountId && $accountId !== 'all') {
            $accountIdsToQuery = [$accountId];
            $selectedAccount = Account::find($accountId);
        } else {
            // যদি "All Accounts" সিলেক্ট করা থাকে বা ডিফল্ট হয়
            $accountIdsToQuery = $accounts->pluck('id')->toArray();
            $selectedAccount = (object) ['name' => 'All Payment Accounts']; // একটি ভার্চুয়াল নাম
        }

        $journalEntries = collect();
        $openingBalance = 0;

        if (! empty($accountIdsToQuery)) {

            // --- ১. প্রারম্ভিক ব্যালেন্স (Opening Balance) গণনা ---
            $openingCredits = JournalEntry::whereIn('account_id', $accountIdsToQuery)
                ->whereHas('transaction', fn ($q) => $q->where('date', '<', $startDate))
                ->sum('credit');
            $openingDebits = JournalEntry::whereIn('account_id', $accountIdsToQuery)
                ->whereHas('transaction', fn ($q) => $q->where('date', '<', $startDate))
                ->sum('debit');

            // যেহেতু আমরা সকল Asset টাইপ পেমেন্ট অ্যাকাউন্ট নিয়ে কাজ করছি, তাই সূত্রটি সহজ
            $openingBalance = $openingDebits - $openingCredits;

            // --- ২. নির্বাচিত তারিখের পরিসরে সকল লেনদেন আনুন ---
            $journalEntries = JournalEntry::with(['transaction', 'account'])
                ->whereIn('account_id', $accountIdsToQuery)
                ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
                ->join('transactions', 'journal_entries.transaction_id', '=', 'transactions.id')
                ->orderBy('transactions.date', 'asc')
                ->orderBy('journal_entries.id', 'asc')
                ->select('journal_entries.*')
                ->get();
        }

        return view('admin.reports.cashbook', [
            'accounts' => $accounts,
            'selectedAccount' => $selectedAccount ?? null,
            'journalEntries' => $journalEntries,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
        ]);
    }

    public function dailyCashbook(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::today();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

        // ১. সকল পেমেন্ট অ্যাকাউন্টের তালিকা
        $accounts = Account::active()->payment()->orderBy('name')->get();
        $accountIds = $accounts->pluck('id')->toArray();

        // ২. নির্দিষ্ট তারিখের সকল জার্নাল এন্ট্রি Raw SQL ব্যবহার করে (N+1 Query এবং IN clause এড়াতে)
        $rawEntries = DB::table('transactions')
            ->join('journal_entries', 'transactions.id', '=', 'journal_entries.transaction_id')
            ->join('accounts', 'journal_entries.account_id', '=', 'accounts.id')
            ->whereBetween('transactions.date', [$startDate, $endDate])
            ->select(
                'transactions.id as transaction_id',
                'journal_entries.id as entry_id',
                'journal_entries.account_id',
                'journal_entries.debit',
                'journal_entries.credit',
                'accounts.type as account_type',
                'accounts.code as account_code',
                'accounts.is_payment_account'
            )
            ->get();

        $groupedEntries = collect($rawEntries)->groupBy('transaction_id');

        // ৩. ক্যাটাগরি ভিত্তিক সামারি (Category Wise Summary)
        $categoryTotals = [
            'savings_collection' => ['inflow' => 0, 'outflow' => 0],
            'loan_collection' => ['inflow' => 0, 'outflow' => 0],
            'loan_disbursement' => ['inflow' => 0, 'outflow' => 0],
            'savings_withdrawal' => ['inflow' => 0, 'outflow' => 0],
            'expenses' => ['inflow' => 0, 'outflow' => 0],
            'interest_income' => ['inflow' => 0, 'outflow' => 0],
            'income' => ['inflow' => 0, 'outflow' => 0],
            'capital_investment' => ['inflow' => 0, 'outflow' => 0],
            'balance_transfer' => ['inflow' => 0, 'outflow' => 0],
            'other' => ['inflow' => 0, 'outflow' => 0],
        ];

        // আজকের লেনদেনের মোট হিসাব রাখার জন্য (অ্যাকাউন্ট ভিত্তিক)
        $todayInflowsByAccount = [];
        $todayOutflowsByAccount = [];

        foreach ($groupedEntries as $transactionId => $allEntries) {
            // এই ট্রানজেকশনে পেমেন্ট অ্যাকাউন্টের এন্ট্রিগুলো বের করি
            $paymentEntries = $allEntries->filter(function ($e) use ($accountIds) {
                return in_array($e->account_id, $accountIds);
            });

            foreach ($paymentEntries as $paymentEntry) {
                $isDebit = $paymentEntry->debit > 0;
                $paymentAmount = $isDebit ? $paymentEntry->debit : $paymentEntry->credit;

                // অ্যাকাউন্ট ভিত্তিক ইন-ফ্লো/আউট-ফ্লো যোগ করি
                if (!isset($todayInflowsByAccount[$paymentEntry->account_id])) $todayInflowsByAccount[$paymentEntry->account_id] = 0;
                if (!isset($todayOutflowsByAccount[$paymentEntry->account_id])) $todayOutflowsByAccount[$paymentEntry->account_id] = 0;

                if ($isDebit) {
                    $todayInflowsByAccount[$paymentEntry->account_id] += $paymentAmount;
                } else {
                    $todayOutflowsByAccount[$paymentEntry->account_id] += $paymentAmount;
                }

                // বিপরীত এন্ট্রিগুলো বের করি
                $oppositeEntries = $allEntries->filter(function ($e) use ($isDebit) {
                    return $isDebit ? $e->credit > 0 : $e->debit > 0;
                });

                if ($oppositeEntries->isEmpty()) continue;

                $totalOppositeAmount = $oppositeEntries->sum(function($e) use ($isDebit) {
                    return $isDebit ? $e->credit : $e->debit;
                });

                foreach ($oppositeEntries as $oe) {
                    $oeAmount = $isDebit ? $oe->credit : $oe->debit;
                    $allocatedAmount = ($oeAmount / $totalOppositeAmount) * $paymentAmount;

                    $cat = 'other';
                    if ($oe->account_type == 'Liability' && $oe->account_code == '2010') {
                        $cat = $isDebit ? 'savings_collection' : 'savings_withdrawal';
                    } elseif ($oe->account_type == 'Asset' && $oe->account_code == '1110') {
                        $cat = $isDebit ? 'loan_collection' : 'loan_disbursement';
                    } elseif ($oe->account_type == 'Income' && $oe->account_code == '4010') {
                        $cat = 'interest_income';
                    } elseif ($oe->account_type == 'Income') {
                        $cat = 'income';
                    } elseif ($oe->account_type == 'Expense') {
                        $cat = 'expenses';
                    } elseif ($oe->account_type == 'Equity') {
                        $cat = 'capital_investment';
                    } elseif ($oe->is_payment_account) {
                        $cat = 'balance_transfer';
                    }

                    if ($isDebit) {
                        $categoryTotals[$cat]['inflow'] += $allocatedAmount;
                    } else {
                        $categoryTotals[$cat]['outflow'] += $allocatedAmount;
                    }
                }
            }
        }

        $categorySummaries = [];
        foreach ($categoryTotals as $name_key => $totals) {
            if ($totals['inflow'] > 0 || $totals['outflow'] > 0) {
                $categorySummaries[] = [
                    'name_key' => $name_key,
                    'inflow' => $totals['inflow'],
                    'outflow' => $totals['outflow'],
                ];
            }
        }

        $totalSavingsCollection = $categoryTotals['savings_collection']['inflow'];
        $totalLoanCollection = $categoryTotals['loan_collection']['inflow'];
        $totalWithdraw = $categoryTotals['savings_withdrawal']['outflow'];
        $totalExpense = $categoryTotals['expenses']['outflow'];
        $totalIncome = $categoryTotals['income']['inflow'];
        $totalInterestIncome = $categoryTotals['interest_income']['inflow'];

        // Get total gross loan collections (Principal + Interest) directly from LoanInstallment
        $totalGrossLoanCollection = \App\Models\LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])->sum('paid_amount');

        // ৪. অ্যাকাউন্ট ভিত্তিক সামারি (Payment Method Wise)
        $paymentMethodSummaries = [];
        $overallOpeningBalance = 0;
        $overallTotalInflow = 0;
        $overallTotalOutflow = 0;

        // Calculate opening balances for all accounts in one query (before start date)
        $openingBalances = DB::table('transactions')
            ->join('journal_entries', 'transactions.id', '=', 'journal_entries.transaction_id')
            ->whereIn('journal_entries.account_id', $accountIds)
            ->where('transactions.date', '<', $startDate)
            ->selectRaw('journal_entries.account_id, SUM(journal_entries.debit) as total_debit, SUM(journal_entries.credit) as total_credit')
            ->groupBy('journal_entries.account_id')
            ->get()
            ->keyBy('account_id');

        foreach ($accounts as $account) {
            // এই অ্যাকাউন্টের প্রারম্ভিক ব্যালেন্স (পূর্বের দিন পর্যন্ত)
            $openingData = $openingBalances->get($account->id);
            $openingDebits = $openingData ? $openingData->total_debit : 0;
            $openingCredits = $openingData ? $openingData->total_credit : 0;

            $opening = $openingDebits - $openingCredits;

            // আজকের লেনদেন
            $inflow = $todayInflowsByAccount[$account->id] ?? 0;
            $outflow = $todayOutflowsByAccount[$account->id] ?? 0;
            $closing = $opening + $inflow - $outflow;

            if ($opening != 0 || $inflow != 0 || $outflow != 0) {
                $paymentMethodSummaries[] = [
                    'account_name' => $account->name,
                    'opening' => $opening,
                    'inflow' => $inflow,
                    'outflow' => $outflow,
                    'closing' => $closing,
                ];
            }

            $overallOpeningBalance += $opening;
            $overallTotalInflow += $inflow;
            $overallTotalOutflow += $outflow;
        }

        $overallClosingBalance = $overallOpeningBalance + $overallTotalInflow - $overallTotalOutflow;



        return view('admin.reports.daily_cashbook', compact(
            'startDate',
            'endDate',
            'categorySummaries',
            'totalSavingsCollection',
            'totalLoanCollection',
            'totalGrossLoanCollection',
            'totalWithdraw',
            'totalExpense',
            'totalIncome',
            'totalInterestIncome',
            'paymentMethodSummaries',
            'overallOpeningBalance',
            'overallTotalInflow',
            'overallTotalOutflow',
            'overallClosingBalance'
        ));
    }

    /**
     * Generate and display the field officer wise collection report.
     * এই মেথডটি প্রতিটি মাঠকর্মীর মোট আদায় (সঞ্চয় ও ঋণ) এবং উত্তোলনের তথ্য দেখাবে।
     */
    public function fieldOfficerWiseReport(Request $request)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::today();

        // সকল মাঠকর্মী এবং তাদের অধীনে নির্দিষ্ট সময়ের লেনদেনের সামারি আনুন
        $fieldOfficers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Field Worker', 'Admin']);
        })
            ->withSum(['loanInstallments as total_loan' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('payment_date', [$startDate, $endDate]);
            }], 'paid_amount')
            ->withSum(['savingsCollections as total_savings' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('collection_date', [$startDate, $endDate]);
            }], 'amount')
            ->withSum(['savingsCollections as total_withdraw' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('collection_date', [$startDate, $endDate]);
            }], 'withdraw_amount')
            ->orderBy('name')
            ->get();

        // গ্র্যান্ড টোটাল হিসাব করুন
        $grandTotalLoan = $fieldOfficers->sum('total_loan');
        $grandTotalSavings = $fieldOfficers->sum('total_savings');
        $grandTotalWithdraw = $fieldOfficers->sum('total_withdraw');

        return view('admin.reports.field_officer_wise', compact(
            'fieldOfficers',
            'startDate',
            'endDate',
            'grandTotalLoan',
            'grandTotalSavings',
            'grandTotalWithdraw'
        ));
    }

    /**
     * Print detailed loan installment collections for a field officer.
     */
    public function printFieldOfficerLoans(Request $request)
    {
        $officer = User::findOrFail($request->officer_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $installments = LoanInstallment::with(['member', 'loanAccount'])
            ->where('collector_id', $officer->id)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'asc')
            ->get();

        return view('admin.reports.prints.officer_loans', compact('officer', 'startDate', 'endDate', 'installments'));
    }

    /**
     * Print detailed savings collections and withdrawals for a field officer.
     */
    public function printFieldOfficerSavings(Request $request)
    {
        $officer = User::findOrFail($request->officer_id);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $collections = SavingsCollection::with(['member', 'savingsAccount'])
            ->where('collector_id', $officer->id)
            ->whereBetween('collection_date', [$startDate, $endDate])
            ->orderBy('collection_date', 'asc')
            ->get();

        return view('admin.reports.prints.officer_savings', compact('officer', 'startDate', 'endDate', 'collections'));
    }

    /**
     * Display members who are not paying loan installments regularly.
     * Criteria: Member not paying for at least 1 day (excluding today).
     */
    public function irregularLoanPayments(Request $request)
    {
        $user = Auth::user();
        $today = \Carbon\Carbon::today();

        // ১. রানিং লোন অ্যাকাউন্টগুলো আনুন যারা কিস্তি দিতে দেরি করেছে (Next Due Date আজকের আগের)
        $query = \App\Models\LoanAccount::with(['member.area'])
            ->withMax('installments as last_payment_date', 'payment_date')
            ->where('status', 'running')
            ->whereDate('next_due_date', '<', $today);

        // ২. ভূমিকা অনুযায়ী ফিল্টার করুন
        if ($user->hasRole('Field Worker')) {
            $areaIds = $user->areas()->pluck('areas.id')->toArray();
            $query->whereHas('member', function ($q) use ($areaIds) {
                $q->whereIn('area_id', $areaIds);
            });
        }

        // ৩. এলাকা অনুযায়ী ফিল্টার (অ্যাডমিন বা ফিল্ড কর্মী উভয়ের জন্য)
        if ($request->filled('area_id')) {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('area_id', $request->area_id);
            });
        }

        // ৪. ডেটা সংগ্রহ এবং ব্যবধান (Gap) হিসাব করা
        $irregularLoans = $query->get()->map(function ($loan) use ($today) {
            // সর্বশেষ কিস্তির তারিখ নিন, যদি না থাকে তবে ঋণ বিতরণের তারিখ ব্যবহার করুন
            $lastDate = $loan->last_payment_date ? \Carbon\Carbon::parse($loan->last_payment_date) : $loan->disbursement_date;

            // ব্যবধান (গ্যাপ) বের করুন
            $gap = $today->diffInDays($lastDate);

            $loan->days_gap = $gap;
            $loan->last_payment_date = $lastDate;

            return $loan;
        })
        ->sortBy(function ($loan) {
            return (int) $loan->account_no;
        });
        if ($user->hasRole('Admin')) {
            $areas = \App\Models\Area::orderBy('name')->get(['id', 'name']);
        } else {
            $areas = $user->areas()->orderBy('name')->get(['areas.id', 'areas.name']);
        }
        
        return view('reports.irregular_loan_payments', compact('irregularLoans', 'areas'));
    }
}
