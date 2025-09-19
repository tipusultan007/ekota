<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // পুরানো ডেটা মুছে ফেলার জন্য (ঐচ্ছিক)
        // DB::table('expense_categories')->truncate();

        $categories = [
            ['name' => 'অফিস ভাড়া (Office Rent)'],
            ['name' => 'কর্মকর্তার বেতন (Employee Salary)'],
            ['name' => 'বিদ্যুৎ বিল (Electricity Bill)'],
            ['name' => 'ইন্টারনেট বিল (Internet Bill)'],
            ['name' => 'সদস্যকে প্রদত্ত মুনাফা (Profit Paid to Members)'],
            ['name' => 'আপ্যায়ন খরচ (Entertainment Expense)'],
            ['name' => 'যাতায়াত খরচ (Transportation Expense)'],
            ['name' => 'মনিহারি দ্রব্যাদি (Stationery)'],
            ['name' => 'অন্যান্য খরচ (Miscellaneous Expense)'],
        ];

        foreach ($categories as $category) {
            // firstOrCreate() ব্যবহার করলে ডুপ্লিকেট এন্ট্রি তৈরি হবে না
            ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }

        $this->command->info('Expense categories seeded successfully!');
    }
}
