<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SavingsCollection;
use App\Models\SavingsWithdrawal;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\LoanInstallment;
use App\Models\JournalEntry;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

$startDate = Carbon::now()->startOfYear();
$endDate = Carbon::now();

echo "Verification for Period: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "\n";
echo "--------------------------------------------------\n";

// 1. Savings Section
$totalSavingsCollected = SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('amount');
$totalSavingsInterestGiven = SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('interest_amount')
                             + SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('profit_amount');
$totalSavingsWithdrawn = SavingsWithdrawal::whereBetween('withdrawal_date', [$startDate, $endDate])->sum('total_amount')
                         + SavingsCollection::whereBetween('collection_date', [$startDate, $endDate])->sum('withdraw_amount');
$currentSavingsLiability = SavingsAccount::sum('current_balance');

echo "Savings Collected: $totalSavingsCollected\n";
echo "Interest Given: $totalSavingsInterestGiven\n";
echo "Savings Withdrawn: $totalSavingsWithdrawn\n";
echo "Savings Liability: $currentSavingsLiability\n";

// 2. Loan Section
$totalLoansDisbursed = LoanAccount::whereBetween('disbursement_date', [$startDate, $endDate])->sum('loan_amount');
$totalLoanPaidInPeriod = LoanInstallment::whereBetween('payment_date', [$startDate, $endDate])->sum('paid_amount');

$interestAccount = Account::where('code', '4010')->first();
$totalInterestGained = $interestAccount ? JournalEntry::where('account_id', $interestAccount->id)
    ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
    ->sum('credit') : 0;

$feeAccount = Account::where('code', '4020')->first();
$totalLoanFees = $feeAccount ? JournalEntry::where('account_id', $feeAccount->id)
    ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
    ->sum('credit') : 0;

$loanOutstanding = LoanAccount::where('status', 'running')->sum(DB::raw('total_payable - total_paid'));

echo "Loans Disbursed: $totalLoansDisbursed\n";
echo "Loans Collected: $totalLoanPaidInPeriod\n";
echo "Interest Gained: $totalInterestGained\n";
echo "Loan Fees: $totalLoanFees\n";
echo "Loan Outstanding: $loanOutstanding\n";

// 3. Income/Expense
$salaryAccount = Account::where('code', '5010')->first();
$totalSalaryExpense = $salaryAccount ? JournalEntry::where('account_id', $salaryAccount->id)
    ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
    ->sum('debit') : 0;

$otherExpenseAccountIds = Account::where('type', 'Expense')->whereNotIn('code', ['5010', '5020', '5030'])->pluck('id');
$totalOtherExpense = JournalEntry::whereIn('account_id', $otherExpenseAccountIds)
    ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
    ->sum('debit');

$otherIncomeAccountIds = Account::where('type', 'Income')->whereNotIn('code', ['4010', '4020'])->pluck('id');
$totalOtherIncome = JournalEntry::whereIn('account_id', $otherIncomeAccountIds)
    ->whereHas('transaction', fn ($q) => $q->whereBetween('date', [$startDate, $endDate]))
    ->sum('credit');

echo "Salary Expense: $totalSalaryExpense\n";
echo "Other Expense: $totalOtherExpense\n";
echo "Other Income: $totalOtherIncome\n";

// 4. Financial Standing
$cashBalance = Account::payment()->active()->get()->sum(fn ($acc) => $acc->balance);
$totalAssets = $cashBalance + $loanOutstanding;
$totalIncome = $totalInterestGained + $totalLoanFees + $totalOtherIncome;
$totalCosts = $totalSalaryExpense + $totalOtherExpense + $totalSavingsInterestGiven;
$netProfitLoss = $totalIncome - $totalCosts;

echo "--------------------------------------------------\n";
echo "Cash Balance: $cashBalance\n";
echo "Total Assets: $totalAssets\n";
echo "Net Profit/Loss: $netProfitLoss\n";
