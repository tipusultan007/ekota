<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    public function definition(): array
    {
        $firstNames = ['রহিম', 'করিম', 'জাব্বার', 'সালাম', 'রফিক', 'কামাল', 'হাসান', 'সাকিব', 'তামিম', 'মুশফিক', 'রিয়াদ', 'সোহেল', 'আরিফ', 'ইমরান'];
        $lastNames = ['খান', 'চৌধুরী', 'শেখ', 'সরকার', 'বিশ্বাস', 'ইসলাম', 'আহমেদ', 'হোসেন', 'উদ্দিন', 'তালুকদার', 'মিয়া', 'পাটোয়ারী'];
        $prefixes = ['017', '018', '019', '016', '015', '013'];

        return [
            'name' => fake()->randomElement($firstNames) . ' ' . fake()->randomElement($lastNames),
            'father_name' => fake()->randomElement($firstNames) . ' ' . fake()->randomElement($lastNames),
            'mother_name' => fake()->randomElement($firstNames) . ' ' . 'বেগম',
            'mobile_no' => fake()->randomElement($prefixes) . fake()->numerify('########'),
            'email' => fake()->unique()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-50 years', '-20 years'),
            'nid_no' => fake()->unique()->numerify('##############'),
            'present_address' => fake()->address(),
            'permanent_address' => fake()->address(),
            'joining_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'status' => 'active',
        ];
    }
}
