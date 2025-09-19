<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //RolesAndPermissionsSeeder::class, // প্রথমে ভূমিকা
            //AreaSeeder::class,                // তারপর এলাকা
            //UserSeeder::class,                // তারপর ব্যবহারকারী
            //MemberSeeder::class,              // তারপর সদস্য
            //SavingsAccountSeeder::class,      // তারপর সঞ্চয় অ্যাকাউন্ট
            ///LoanAccountSeeder::class,         // সবশেষে ঋণ অ্যাকাউন্ট
            DemoDataSeeder::class,
        ]);
    }
}
