<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // database/seeders/AccountSeeder.php

    public function run(): void
    {
        $this->command->info('Seeding the Chart of Accounts in Bengali...');

        $accounts = [
            // =======================================================
            // ============== সম্পদ (Assets) ==============
            // =======================================================
            // চলতি সম্পদ (Current Assets)
            ['code' => '1010', 'name' => 'হাতে নগদ (Cash in Hand)', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => true],
            ['code' => '1020', 'name' => 'ব্যাংক হিসাব (Bank Accounts)', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => false],
            ['code' => '1030', 'name' => 'মোবাইল ব্যাংকিং (Mobile Banking)', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => false],

            // প্রাপ্য হিসাব (Accounts Receivable)
            ['code' => '1110', 'name' => 'প্রাপ্য ঋণ (Loans Receivable)', 'type' => 'Asset', 'is_payment_account' => false, 'is_system_account' => true],

            // =======================================================
            // ============== দায় (Liabilities) ==============
            // =======================================================
            // চলতি দায় (Current Liabilities)
            ['code' => '2010', 'name' => 'সদস্যদের সঞ্চয় (Members\' Savings Payable)', 'type' => 'Liability', 'is_payment_account' => false, 'is_system_account' => true],

            // =======================================================
            // ============== মালিকানা সত্তা (Owner's Equity) ==============
            // =======================================================
            ['code' => '3010', 'name' => 'বিনিয়োগকৃত মূলধন (Capital Investment)', 'type' => 'Equity', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '3020', 'name' => 'অর্জিত মুনাফা (Retained Earnings)', 'type' => 'Equity', 'is_payment_account' => false, 'is_system_account' => true],

            // =======================================================
            // ============== আয় (Income) ==============
            // =======================================================
            ['code' => '4010', 'name' => 'ঋণের সুদ আয় (Interest Income)', 'type' => 'Income', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '4020', 'name' => 'ঋণ প্রক্রিয়াকরণ ফি আয় (Processing Fee Income)', 'type' => 'Income', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '4030', 'name' => 'বিবিধ আয় (General Income)', 'type' => 'Income', 'is_payment_account' => false, 'is_system_account' => true],

            // =======================================================
            // ============== ব্যয় (Expenses) ==============
            // =======================================================
            ['code' => '5010', 'name' => 'কর্মকর্তার বেতন (Employee Salary)', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5020', 'name' => 'সদস্যদের প্রদত্ত মুনাফা (Profit Paid to Members)', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5030', 'name' => 'ঋণ মওকুফ/ছাড় (Loan Grace / Discount)', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5040', 'name' => 'অফিস ভাড়া (Office Rent)', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => false],
            ['code' => '5999', 'name' => 'বিবিধ খরচ (General Expense)', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => false],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
