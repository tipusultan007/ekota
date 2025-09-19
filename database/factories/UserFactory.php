<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->unique()->numerify('01#########'),
            'address' => fake()->address(),
            'nid_no' => fake()->unique()->numerify('##############'),
            'joining_date' => fake()->dateTimeBetween('-3 years', 'now'),
            'status' => 'active',
            'salary' => fake()->randomElement([12000, 15000, 18000, 20000]),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
