<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class LoanAccountFactory extends Factory
{
    public function definition(): array
    {
        $loanAmount = fake()->randomElement([50000, 75000, 100000, 150000, 200000]);
        $interestRate = 10;
        $installments = fake()->randomElement([12, 24, 36]);
        $interest = ($loanAmount * $interestRate) / 100;
        $totalPayable = $loanAmount + $interest;
        $frequency = fake()->randomElement(['daily', 'weekly', 'monthly']);
        $disbursementDate = fake()->dateTimeBetween('-2 years', '-1 month');
        $nextDate = Carbon::parse($disbursementDate);

        if ($frequency == 'daily') {
            $nextDate->addDay();
        } elseif ($frequency == 'weekly') {
            $nextDate->addWeek();
        } else {
            $nextDate->addMonth();
        }

        return [
            'account_no' => 'LN-' . fake()->unique()->numerify('######'),
            'loan_amount' => $loanAmount,
            'interest_rate' => $interestRate,
            'number_of_installments' => $installments,
            'installment_amount' => $totalPayable / $installments,
            'disbursement_date' => $disbursementDate,
            'total_payable' => $totalPayable,
            'total_paid' => 0, // সিডার থেকে আপডেট হবে
            'status' => 'running',
            'installment_frequency' => $frequency,
            'next_due_date' => $nextDate,
        ];
    }
}
