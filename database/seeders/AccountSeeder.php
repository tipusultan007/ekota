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
    public function run(): void
    {
        $this->command->info('Seeding the Chart of Accounts...');

        $accounts = [
            // --- ASSETS (সম্পদ) ---
            // Current Assets
            ['code' => '1010', 'name' => 'Cash in Hand', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => true],
            ['code' => '1020', 'name' => 'Bank Accounts', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => false], // ব্যবহারকারী আরও ব্যাংক অ্যাকাউন্ট যোগ করতে পারবেন
            ['code' => '1030', 'name' => 'Mobile Banking', 'type' => 'Asset', 'is_payment_account' => true, 'is_system_account' => false],
            // Loans & Receivables
            ['code' => '1110', 'name' => 'Loans Receivable', 'type' => 'Asset', 'is_payment_account' => false, 'is_system_account' => true],

            // --- LIABILITIES (দায়) ---
            // Current Liabilities
            ['code' => '2010', 'name' => 'Members\' Savings Payable', 'type' => 'Liability', 'is_payment_account' => false, 'is_system_account' => true],
            
            // --- EQUITY (মালিকানা সত্তা) ---
            ['code' => '3010', 'name' => 'Capital Investment', 'type' => 'Equity', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'Equity', 'is_payment_account' => false, 'is_system_account' => true],

            // --- INCOME (আয়) ---
            ['code' => '4010', 'name' => 'Interest Income', 'type' => 'Income', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '4020', 'name' => 'Loan Processing Fee Income', 'type' => 'Income', 'is_payment_account' => false, 'is_system_account' => true],

            // --- EXPENSES (ব্যয়) ---
            ['code' => '5010', 'name' => 'Employee Salary', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5020', 'name' => 'Profit Paid to Members', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5030', 'name' => 'Loan Grace / Discount', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => true],
            ['code' => '5040', 'name' => 'Office Rent', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => false], // ব্যবহারকারী এই ধরনের খাত ডিলিট/এডিট করতে পারবেন
            ['code' => '5999', 'name' => 'Miscellaneous Expense', 'type' => 'Expense', 'is_payment_account' => false, 'is_system_account' => false],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['code' => $account['code']], // কোড ইউনিক হতে হবে
                $account
            );
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
