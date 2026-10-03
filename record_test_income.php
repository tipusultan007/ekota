<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Account;
use App\Services\AccountingService;
use Illuminate\Support\Facades\DB;

$category = IncomeCategory::firstOrCreate(['name' => 'General Testing']);
$cashAccount = Account::where('code', '1010')->first();
$incomeAccount = Account::where('code', '4030')->first();

DB::transaction(function () use ($category, $cashAccount, $incomeAccount) {
    $amount = 5000;
    $income = Income::create([
        'income_category_id' => $category->id,
        'account_id' => $cashAccount->id,
        'amount' => $amount,
        'income_date' => now(),
        'description' => 'Verification Income Entry',
        'user_id' => 1
    ]);

    $service = new AccountingService();
    $service->createTransaction(
        now()->format('Y-m-d'),
        'Income Received: Verification Income Entry',
        [
            ['account_id' => $cashAccount->id, 'debit' => $amount],
            ['account_id' => $incomeAccount->id, 'credit' => $amount]
        ],
        $income
    );
});

echo "Income recorded successfully.\n";
