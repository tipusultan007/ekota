<?php

namespace Database\Factories;

use App\Models\LoanAccount;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guarantor>
 */
class GuarantorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // এই ফ্যাক্টরিটি সাধারণত সিডার থেকে loan_account_id এবং member_id পাবে।
        // তবে, সরাসরি ফ্যাক্টরি কল করলে যাতে কাজ করে, তার জন্য আমরা কিছু প্লেসহোল্ডার লজিক রাখতে পারি।
        return [
            'loan_account_id' => LoanAccount::factory(),
            'member_id' => Member::factory(),
            'name' => null,      // যেহেতু আমরা সদস্যকেই জামিনদার ধরছি
            'phone' => null,
            'address' => null,
        ];
    }

    /**
     * Indicate that the guarantor is an outside person.
     * এই স্টেটটি বাইরের জামিনদার তৈরির জন্য ব্যবহার করা যেতে পারে।
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function outsider(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'member_id' => null, // বাইরের জামিনদারের কোনো সদস্য আইডি থাকবে না
                'name' => fake()->name(),
                'phone' => fake()->numerify('01#########'),
                'address' => fake()->address(),
            ];
        });
    }
}
