<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class SavingsAccountFactory extends Factory
{
    public function definition(): array
    {
        $openingDate = fake()->dateTimeBetween('-3 years', 'now');
        $frequency = fake()->randomElement(['daily', 'weekly', 'monthly']);
        $nextDate = Carbon::parse($openingDate);

        if ($frequency == 'daily') {
            $nextDate->addDay();
        } elseif ($frequency == 'weekly') {
            $nextDate->addWeek();
        } else {
            $nextDate->addMonth();
        }

        $installments = [100,500,1000,1500,2000];
        return [
            'account_no' => 'SV-' . fake()->unique()->numerify('#######'),
            'scheme_type' => fake()->randomElement(['daily', 'weekly', 'monthly','dps']),
            'interest_rate' => fake()->randomFloat(2, 5, 9),
            'current_balance' => 0, // সিডার থেকে আপডেট হবে
            'opening_date' => $openingDate,
            'status' => 'active',
            'installment' => fake()->randomElement($installments),
            'nominee_name' => fake()->name(),
            'nominee_relation' => fake()->randomElement(['Son', 'Daughter', 'Spouse', 'Father', 'Mother']),
            'nominee_phone' => fake()->numerify('01#########'),
            'collection_frequency' => $frequency,
            'next_due_date' => $nextDate,
        ];
    }
}
