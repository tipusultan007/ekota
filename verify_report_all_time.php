<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;

$interest = JournalEntry::where('account_id', 8)->sum('credit');
$fees = JournalEntry::where('account_id', 9)->sum('credit');

echo "All Time Totals:\n";
echo "Interest Earned: $interest\n";
echo "Loan Fees: $fees\n";
