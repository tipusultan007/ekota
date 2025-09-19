<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\LoanAccount;
use App\Models\Guarantor;

class LoanAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $allMembers = Member::where('status', 'active')->get();

        if ($allMembers->count() < 2) {
            $this->command->warn('Not enough active members found to create loans (requires at least 2).');
            return;
        }

        // মোট সদস্যের ৩০% কে ঋণ দিন
        $loanRecipients = $allMembers->random(floor($allMembers->count() * 0.3));

        $this->command->info('Creating loan accounts for ' . $loanRecipients->count() . ' members...');

        foreach ($loanRecipients as $borrower) {
            // জামিনদার হিসেবে ঋণগ্রহীতা ছাড়া অন্য যেকোনো সদস্যকে বেছে নিন
            $guarantorMember = $allMembers->where('id', '!=', $borrower->id)->random();

            $loanAmount = fake()->randomElement([50000, 75000, 100000, 150000, 200000]);
            $interestRate = 10.00;
            $installments = 24;

            $interest = ($loanAmount * $interestRate) / 100;
            $totalPayable = $loanAmount + $interest;
            $installmentAmount = $totalPayable / $installments;

            // ১. ঋণ অ্যাকাউন্ট তৈরি করুন
            $loanAccount = LoanAccount::create([
                'member_id' => $borrower->id,
                'account_no' => 'LN-' . $borrower->id . '-' . time(),
                'loan_amount' => $loanAmount,
                'interest_rate' => $interestRate,
                'number_of_installments' => $installments,
                'disbursement_date' => fake()->dateTimeBetween($borrower->joining_date, 'now'),
                'total_payable' => $totalPayable,
                'installment_amount' => $installmentAmount,
                'total_paid' => fake()->randomFloat(2, 0, $totalPayable * 0.5), // ঋণের ০% থেকে ৫০% পর্যন্ত পরিশোধিত
                'status' => 'running',
            ]);

            // ২. জামিনদার যুক্ত করুন
            Guarantor::create([
                'loan_account_id' => $loanAccount->id,
                'member_id' => $guarantorMember->id, // জামিনদার হিসেবে একজন বিদ্যমান সদস্য
            ]);
        }

        $this->command->info('Successfully created loan accounts.');
    }
}
