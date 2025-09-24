<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Area;
use App\Models\Account;
use App\Models\User;
use App\Models\Member;
use App\Models\ExpenseCategory;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\Guarantor;
use App\Models\SavingsCollection;
use App\Models\LoanInstallment;
use App\Models\Expense;
use App\Models\CapitalInvestment;
use App\Services\AccountingService; // অ্যাকাউন্টিং সার্ভিস
use Carbon\Carbon;
use Spatie\Permission\Models\Role as ModelsRole;

class DemoDataSeeder extends Seeder
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // ক্যাশ রিসেট করুন
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Seeding Demo Data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // টেবিল ট্রাঙ্কেট করুন
        DB::table('users')->truncate();
        DB::table('areas')->truncate();
        DB::table('members')->truncate();
        DB::table('accounts')->truncate();
        DB::table('expense_categories')->truncate();
        DB::table('savings_accounts')->truncate();
        DB::table('loan_accounts')->truncate();
        DB::table('guarantors')->truncate();
        DB::table('savings_collections')->truncate();
        DB::table('loan_installments')->truncate();
        DB::table('expenses')->truncate();
        DB::table('transactions')->truncate();
        DB::table('roles')->truncate();
        DB::table('model_has_roles')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- ১. ভূমিকা তৈরি ---
        $adminRole = ModelsRole::create(['name' => 'Admin']);
        $fieldWorkerRole = ModelsRole::create(['name' => 'Field Worker']);
        $this->command->info('Roles Created.');

        // --- ২. এলাকা তৈরি ---
        $areas = Area::factory()->count(5)->sequence(
            ['name' => 'ধানমন্ডি শাখা'],
            ['name' => 'গুলশান শাখা'],
            ['name' => 'উত্তরা শাখা'],
            ['name' => 'চট্টগ্রাম শাখা'],
            ['name' => 'সিলেট শাখা']
        )->create();
        $this->command->info('Areas Created.');

        // --- ৩. Chart of Accounts সিড করুন ---
        $this->call(AccountSeeder::class);
        // অ্যাকাউন্টের আইডিগুলো ভেরিয়েবলে নিয়ে নিন
        $cashAccount = Account::where('code', '1010')->first();
        $bankAccount = Account::where('code', '1020')->first();
        $loansReceivableAccount = Account::where('code', '1110')->first();
        $savingsPayableAccount = Account::where('code', '2010')->first();
        $capitalAccount = Account::where('code', '3010')->first();
        $interestIncomeAccount = Account::where('code', '4010')->first();
        $feeIncomeAccount = Account::where('code', '4020')->first();
        $this->command->info('Chart of Accounts Seeded.');

        // --- ৪. ব্যবহারকারী (অ্যাডমিন ও মাঠকর্মী) তৈরি ---
        $adminUser = User::factory()->create(['name' => 'Admin User', 'email' => 'admin@samiti.com']);
        $adminUser->assignRole($adminRole);
        $fieldWorkers = User::factory()->count(5)->create()->each(fn($user, $i) => $user->areas()->attach($areas[$i]->id));
        $this->command->info('Admin and Field Workers Created.');

        // --- ৫. প্রাথমিক মূলধন বিনিয়োগ ---
        $investment = CapitalInvestment::create([
            'user_id' => $adminUser->id,
            'account_id' => $bankAccount->id,
            'amount' => 10000000, // ১ কোটি
            'investment_date' => Carbon::today()->subYear(),
        ]);
        $this->accountingService->createTransaction(
            $investment->investment_date,
            'Initial Capital Investment',
            $investment->amount,
            $bankAccount->id, // Debit (Asset increases)
            $capitalAccount->id, // Credit (Equity increases)
            $investment
        );
        $this->command->info('Initial Capital Invested.');

        // --- ৬. সদস্য তৈরি ---
        $members = Member::factory()->count(100)->create(['area_id' => $areas->random()->id]);
        $this->command->info('Members Created.');

        // --- ৭. সঞ্চয়, ঋণ এবং লেনদেন তৈরি ---
        $this->command->withProgressBar($members, function ($member) use ($cashAccount, $bankAccount, $loansReceivableAccount, $savingsPayableAccount, $interestIncomeAccount, $feeIncomeAccount, $members, $fieldWorkers) {

            // ক) প্রতিটি সদস্যের জন্য একটি সঞ্চয় অ্যাকাউন্ট এবং কিছু জমা
            $savingsAccount = SavingsAccount::factory()->create(['member_id' => $member->id, 'current_balance' => 0]);
            for ($i = 0; $i < rand(5, 15); $i++) {
                $amount = rand(200, 1000);
                $date = Carbon::today()->subDays(rand(1, 300));
                $collection = $savingsAccount->collections()->create(['member_id' => $member->id, 'collector_id' => $fieldWorkers->random()->id, 'amount' => $amount, 'collection_date' => $date]);
                $this->accountingService->createTransaction($date, 'Savings deposit from ' . $member->name, $amount, $cashAccount->id, $savingsPayableAccount->id, $collection);
            }

            // খ) ৩০% সদস্যকে একটি করে ঋণ দিন
            if (rand(1, 10) <= 3) {
                $loanAccount = LoanAccount::factory()->create(['member_id' => $member->id, 'processing_fee' => 500, 'total_paid' => 0]);
                Guarantor::factory()->create(['loan_account_id' => $loanAccount->id, 'member_id' => $members->where('id', '!=', $member->id)->random()->id]);

                // ঋণ বিতরণের লেনদেন
                $this->accountingService->createTransaction($loanAccount->disbursement_date, 'Loan disbursed to ' . $member->name, $loanAccount->loan_amount, $loansReceivableAccount->id, $bankAccount->id, $loanAccount);

                // প্রক্রিয়াকরণ ফি আয়
                $this->accountingService->createTransaction($loanAccount->disbursement_date, 'Processing fee from ' . $member->name, $loanAccount->processing_fee, $bankAccount->id, $feeIncomeAccount->id, $loanAccount);

                // কিছু কিস্তি জমা দিন
                $paidInstallments = rand(1, $loanAccount->number_of_installments / 2);
                for ($i = 0; $i < $paidInstallments; $i++) {
                    $amount = $loanAccount->installment_amount;
                    $date = Carbon::parse($loanAccount->disbursement_date)->addMonths($i + 1);
                    $installment = $loanAccount->installments()->create(['member_id' => $member->id, 'collector_id' => $fieldWorkers->random()->id, 'installment_no' => $i + 1, 'paid_amount' => $amount, 'payment_date' => $date]);

                    // কিস্তির আসল এবং সুদ আলাদা করুন
                    $principalPart = $amount * ($loanAccount->loan_amount / $loanAccount->total_payable);
                    $interestPart = $amount - $principalPart;

                    // আসল পরিশোধের লেনদেন
                    $this->accountingService->createTransaction($date, 'Loan principal collected', $principalPart, $cashAccount->id, $loansReceivableAccount->id, $installment);

                    // সুদ আয়ের লেনদেন
                    $this->accountingService->createTransaction($date, 'Interest income collected', $interestPart, $cashAccount->id, $interestIncomeAccount->id, $installment);
                }
            }
        });
        $this->command->info("\nSavings/Loan Accounts and Transactions Created.");

        // --- ৮. ব্যালেন্স সিঙ্ক করুন ---
        $this->command->call('account:sync-balances');
    }
}
