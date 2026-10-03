<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\Account;
use Carbon\Carbon;

$start = Carbon::now()->startOfMonth()->format('Y-m-d');
$end = Carbon::now()->endOfMonth()->format('Y-m-d');

$interest = JournalEntry::where('account_id', 8)
    ->whereHas('transaction', fn($q) => $q->whereBetween('date', [$start, $end]))
    ->sum('credit');

$fees = JournalEntry::where('account_id', 9)
    ->whereHas('transaction', fn($q) => $q->whereBetween('date', [$start, $end]))
    ->sum('credit');

$otherIncomeAccountIds = Account::where('type', 'Income')
    ->whereNotIn('code', ['4010', '4020'])
    ->pluck('id');

$other = JournalEntry::whereIn('account_id', $otherIncomeAccountIds)
    ->whereHas('transaction', fn($q) => $q->whereBetween('date', [$start, $end]))
    ->sum('credit');

echo "Period: $start to $end\n";
echo "Interest Earned: $interest\n";
echo "Loan Fees: $fees\n";
echo "Other Income: $other\n";
