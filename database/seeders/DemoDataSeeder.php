<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
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
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
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
        $adminRole = Role::create(['name' => 'Admin']);
        $fieldWorkerRole = Role::create(['name' => 'Field Worker']);
        $this->command->info('Roles Created.');

        // --- ২. এলাকা তৈরি ---
        $areas = Area::factory()->count(5)->sequence(
            ['name' => 'ধানমন্ডি শাখা', 'code' => 'DHA'],
            ['name' => 'গুলশান শাখা', 'code' => 'GUL'],
            ['name' => 'চট্টগ্রাম শাখা', 'code' => 'CTG'],
            ['name' => 'সিলেট শাখা', 'code' => 'SYL'],
            ['name' => 'খুলনা শাখা', 'code' => 'KHL']
        )->create();
        $this->command->info('Areas Created.');

        // --- ৩. আর্থিক অ্যাকাউন্ট তৈরি ---
        $cashAccount = Account::create(['name' => 'Cash in Hand', 'balance' => 1000000]);
        $bankAccount = Account::create(['name' => 'DBBL Bank Account', 'balance' => 5000000]);
        $this->command->info('Financial Accounts Created.');

        // --- ৪. ব্যবহারকারী (অ্যাডমিন ও মাঠকর্মী) তৈরি ---
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@samiti.com',
        ]);
        $adminUser->assignRole($adminRole);

        $fieldWorkers = User::factory()->count(5)->create()->each(function ($user, $index) use ($fieldWorkerRole, $areas) {
            $user->assignRole($fieldWorkerRole);
            $user->areas()->attach($areas[$index]->id); // প্রত্যেককে একটি করে এলাকা দিন
        });
        $this->command->info('Admin and Field Workers Created.');

        // --- ৫. সদস্য তৈরি ---
//        $members = Member::factory()->count(100)->create([
//            'area_id' => $areas->random()->id
//        ]);
/*        $members = [];
        $areas->each(function ($area) {
            $members[] = Member::factory()->count(20)->create([
                'area_id' => $area->id,
            ]);
        });*/
        $members = Member::factory()->count(100)->create([
            'area_id' => function () use ($areas) {
                return $areas->random()->id;
            }
        ]);

        $this->command->info('Members Created.');

        // --- ৬. খরচের খাত তৈরি ---
        $salaryCategory = ExpenseCategory::create(['name' => 'Employee Salary']);
        $rentCategory = ExpenseCategory::create(['name' => 'Office Rent']);
        $this->command->info('Expense Categories Created.');

        // --- ৭. সঞ্চয় ও ঋণ অ্যাকাউন্ট এবং লেনদেন তৈরি ---
        $this->command->withProgressBar($members, function ($member) use ($cashAccount,$bankAccount, $members, $fieldWorkers) {

            // ক) প্রতিটি সদস্যের জন্য একটি সঞ্চয় অ্যাকাউন্ট
            $savingsAccount = SavingsAccount::factory()->create([
                'member_id' => $member->id,
                'current_balance' => 0 // শুরুতে ব্যালেন্স ০
            ]);

            // খ) কিছু প্রাথমিক সঞ্চয় জমা (লেনদেন সহ)
            for ($i=0; $i < rand(5, 20); $i++) {
                $amount = rand(200, 1000);
                $date = Carbon::today()->subDays(rand(1, 365));
                $collection = SavingsCollection::create([
                    'savings_account_id' => $savingsAccount->id,
                    'member_id' => $member->id,
                    'collector_id' => $fieldWorkers->random()->id,
                    'amount' => $amount,
                    'collection_date' => $date,
                ]);
                $savingsAccount->increment('current_balance', $amount);

                // অ্যাকাউন্টিং এন্ট্রি
                $collection->transactions()->create([
                    'account_id' => $cashAccount->id,
                    'savings_account_id' => $savingsAccount->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'transaction_date' => $date,
                    'description' => 'Savings deposit from ' . $member->name
                ]);
                $cashAccount->increment('balance', $amount);
            }

            // গ) ৩০% সদস্যকে একটি করে ঋণ দিন
            if (rand(1, 10) <= 3) {
                $loanAccount = LoanAccount::factory()->create([
                    'member_id' => $member->id,
                    'total_paid' => 0 // শুরুতে ০
                ]);
                Guarantor::factory()->create([
                    'loan_account_id' => $loanAccount->id,
                    'member_id' => $members->where('id', '!=', $member->id)->random()->id
                ]);

                // ঋণ বিতরণের লেনদেন
                $loanAccount->transactions()->create([
                    'account_id' => $bankAccount->id, // ব্যাংক থেকে ঋণ দিন
                    'type' => 'debit',
                    'amount' => $loanAccount->loan_amount,
                    'transaction_date' => $loanAccount->disbursement_date,
                    'description' => 'Loan disbursed to ' . $member->name
                ]);
                $bankAccount->decrement('balance', $loanAccount->loan_amount);

                // কিছু কিস্তি জমা দিন
                $paidInstallments = rand(1, $loanAccount->number_of_installments - 5);
                for ($i=0; $i < $paidInstallments; $i++) {
                    $amount = $loanAccount->installment_amount;
                    $date = Carbon::parse($loanAccount->disbursement_date)->addMonths($i + 1);

                    $installment = LoanInstallment::create([
                        'loan_account_id' => $loanAccount->id,
                        'member_id' => $member->id,
                        'collector_id' => $fieldWorkers->random()->id,
                        'paid_amount' => $amount,
                        'payment_date' => $date,
                        'installment_no' => $i + 1, // কিস্তির নম্বর যোগ করুন

                    ]);
                    $loanAccount->increment('total_paid', $amount);

                    // অ্যাকাউন্টিং এন্ট্রি
                    $installment->transactions()->create([
                        'account_id' => $cashAccount->id,
                        'type' => 'credit',
                        'amount' => $amount,
                        'transaction_date' => $date,
                        'description' => 'Loan installment from ' . $member->name
                    ]);
                    $cashAccount->increment('balance', $amount);
                }
            }
        });
        $this->command->info("\nSavings/Loan Accounts and Transactions Created.");

        // --- ৮. কিছু সাধারণ খরচ তৈরি ---
        Expense::factory()->count(15)->create([
            'expense_category_id' => $rentCategory->id,
            'user_id' => $adminUser->id
        ])->each(function ($expense) use ($cashAccount) {
            $expense->transactions()->create([
                'account_id' => $cashAccount->id,
                'type' => 'debit',
                'amount' => $expense->amount,
                'transaction_date' => $expense->expense_date,
                'description' => 'Expense: ' . $expense->category->name
            ]);
            $cashAccount->decrement('balance', $expense->amount);
        });
        $this->command->info('General Expenses Created.');

        $this->command->info('Demo data seeding completed successfully!');
    }
}
