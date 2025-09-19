<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\SavingsAccount;

class SavingsAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $members = Member::where('status', 'active')->get();

        if ($members->isEmpty()) {
            $this->command->warn('No active members found. Please run MemberSeeder first.');
            return;
        }

        $this->command->info('Creating savings accounts for ' . $members->count() . ' members...');

        foreach ($members as $member) {
            // ১. প্রতিটি সদস্যের জন্য একটি করে সাধারণ সঞ্চয় অ্যাকাউন্ট তৈরি করুন
            SavingsAccount::create([
                'member_id' => $member->id,
                'account_no' => 'GS-' . $member->id . '-' . time(),
                'scheme_type' => 'General Savings',
                'interest_rate' => 5.50,
                'current_balance' => fake()->randomFloat(2, 500, 10000), // 500 থেকে 10000 এর মধ্যে র‍্যান্ডম ব্যালেন্স
                'opening_date' => fake()->dateTimeBetween($member->joining_date, 'now'),
                'status' => 'active',
                'nominee_name' => fake()->name(),
                'nominee_relation' => fake()->randomElement(['Son', 'Daughter', 'Spouse', 'Father']),
                'nominee_phone' => fake()->numerify('01#########'),
            ]);

            // ২. ৫০% সদস্যের জন্য একটি করে মাসিক ডিপিএস অ্যাকাউন্ট তৈরি করুন
            if (rand(0, 1) == 1) {
                SavingsAccount::create([
                    'member_id' => $member->id,
                    'account_no' => 'DPS-' . $member->id . '-' . time(),
                    'scheme_type' => 'Monthly DPS',
                    'interest_rate' => 8.00,
                    'current_balance' => fake()->randomFloat(2, 2000, 25000),
                    'opening_date' => fake()->dateTimeBetween($member->joining_date, 'now'),
                    'status' => 'active',
                    'nominee_name' => fake()->name(),
                    'nominee_relation' => fake()->randomElement(['Son', 'Daughter', 'Spouse', 'Mother']),
                    'nominee_phone' => fake()->numerify('01#########'),
                ]);
            }
        }

        $this->command->info('Successfully created savings accounts.');
    }
}
