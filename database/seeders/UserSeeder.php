<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // প্রথমে প্রয়োজনীয় ভূমিকা ও এলাকা আছে কিনা তা যাচাই করুন
        $fieldWorkerRole = Role::where('name', 'Field Worker')->first();
        $areas = Area::all();

        // যদি ভূমিকা বা এলাকা না পাওয়া যায়, তাহলে একটি সতর্কবার্তা দিয়ে সিডার বন্ধ করুন
        if (!$fieldWorkerRole || $areas->count() < 3) {
            $this->command->warn('Field Worker role not found or not enough areas available (requires at least 3).');
            $this->command->warn('Please run RolesAndPermissionsSeeder and AreaSeeder first.');
            return;
        }

        // --- প্রথম মাঠকর্মী তৈরি করুন ---
        $kamal = User::factory()->create([
            'name' => 'কামাল হোসেন',
            'email' => 'kamal@samiti.com',
            // ফ্যাক্টরি থেকে বাকি ডেমো ডেটা আসবে
        ]);

        // তাকে 'Field Worker' ভূমিকা দিন
        $kamal->assignRole($fieldWorkerRole);

        // তাকে দুটি এলাকা এসাইন করুন (প্রথম এবং দ্বিতীয় এলাকা)
        $kamal->areas()->attach([
            $areas[0]->id, // যেমন: ধানমন্ডি শাখা
            $areas[1]->id, // যেমন: গুলশান শাখা
        ]);
        $this->command->info('Created Field Worker: Kamal Hossain, assigned to 2 areas.');

        // --- দ্বিতীয় মাঠকর্মী তৈরি করুন ---
        $rahima = User::factory()->create([
            'name' => 'রহিমা বেগম',
            'email' => 'rahima@samiti.com',
            // ফ্যাক্টরি থেকে বাকি ডেমো ডেটা আসবে
        ]);

        // তাকে 'Field Worker' ভূমিকা দিন
        $rahima->assignRole($fieldWorkerRole);

        // তাকে একটি এলাকা এসাইন করুন (তৃতীয় এলাকা)
        $rahima->areas()->attach($areas[2]->id); // যেমন: চট্টগ্রাম শাখা
        $this->command->info('Created Field Worker: Rahima Begum, assigned to 1 area.');
    }
}
