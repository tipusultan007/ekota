<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'amount' => fake()->randomFloat(2, 500, 5000),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'description' => fake()->sentence(),
        ];
    }
}
