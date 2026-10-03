<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Area;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // --- ভূমিকাগুলো আছে কিনা তা যাচাই করুন ---
        $adminRole = Role::where('name', 'Admin')->first();
        $fieldWorkerRole = Role::where('name', 'Field Worker')->first();

        if (!$adminRole || !$fieldWorkerRole) {
            $this->command->error('Roles "Admin" or "Field Worker" not found. Please run RolesAndPermissionsSeeder first.');
            return;
        }

        // --- ১. ডিফল্ট অ্যাডমিন ব্যবহারকারী তৈরি করুন ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@samiti.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '01700000000',
                'joining_date' => now(),
                'status' => 'active',
            ]
        );
        $admin->assignRole($adminRole);
        $this->command->info('Admin user created successfully. Email: admin@samiti.com, Password: password');


        // --- ২. ডেমো মাঠকর্মী তৈরি করুন ---
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->warn('No areas found. Skipping creation of field workers.');
            return;
        }

        // প্রথম মাঠকর্মী
        $worker1 = User::firstOrCreate(
            ['email' => 'worker1@samiti.com'],
            [
                'name' => 'Kamal Hossain (Field Worker)',
                'password' => Hash::make('password'),
                'phone' => '01800000001',
                'joining_date' => now()->subMonths(3),
                'status' => 'active',
                'salary' => 15000,
            ]
        );
        $worker1->assignRole($fieldWorkerRole);
        // তাকে দুটি এলাকা দিন (যদি দুটি এলাকা থাকে)
        if ($areas->has(0) && $areas->has(1)) {
            $worker1->areas()->sync([$areas[0]->id, $areas[1]->id]);
        }
        $this->command->info('Field worker 1 created.');

        // দ্বিতীয় মাঠকর্মী
        $worker2 = User::firstOrCreate(
            ['email' => 'worker2@samiti.com'],
            [
                'name' => 'Rahima Begum (Field Worker)',
                'password' => Hash::make('password'),
                'phone' => '01900000002',
                'joining_date' => now()->subMonths(6),
                'status' => 'active',
                'salary' => 12000,
            ]
        );
        $worker2->assignRole($fieldWorkerRole);
        // তাকে একটি এলাকা দিন (যদি থাকে)
        if ($areas->has(2)) {
            $worker2->areas()->sync([$areas[2]->id]);
        }
        $this->command->info('Field worker 2 created.');
    }
}
