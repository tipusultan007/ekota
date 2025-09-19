<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // পুরানো ডেটা মুছে ফেলার জন্য (ঐচ্ছিক, কিন্তু ফ্রেশ মাইগ্রেশনের জন্য ভালো)
        // DB::table('accounts')->truncate();

        $accounts = [
            [
                'name' => 'Cash in Hand',
                'balance' => 0, // উদাহরণস্বরূপ প্রারম্ভিক ক্যাশ ব্যালেন্স
                'details' => 'Main cash box for daily transactions.',
                'is_active' => true,
            ],
            [
                'name' => 'Dutch-Bangla Bank Ltd.',
                'balance' => 0, // উদাহরণস্বরূপ প্রারম্ভিক ব্যাংক ব্যালেন্স
                'details' => 'A/C: 123-456-7890, Dhanmondi Branch',
                'is_active' => true,
            ],
            [
                'name' => 'bKash Merchant',
                'balance' => 0, // উদাহরণস্বরূপ প্রারম্ভিক বিকাশ ব্যালেন্স
                'details' => 'Merchant Account: 01712345678',
                'is_active' => true,
            ],
            [
                'name' => 'Suspense Account',
                'balance' => 0.00,
                'details' => 'For temporary or unclassified transactions.',
                'is_active' => false,
            ],
        ];

        foreach ($accounts as $account) {
            // firstOrCreate() ব্যবহার করলে ডুপ্লিকেট অ্যাকাউন্ট তৈরি হবে না
            Account::firstOrCreate(['name' => $account['name']], $account);
        }

        $this->command->info('Default accounts (Chart of Accounts) seeded successfully!');
    }
}
