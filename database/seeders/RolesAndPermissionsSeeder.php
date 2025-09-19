<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ক্যাশ রিসেট করুন
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ভূমিকা তৈরি করুন
        $adminRole = Role::create(['name' => 'Admin']);
        $fieldWorkerRole = Role::create(['name' => 'Field Worker']);

        // একটি ডিফল্ট অ্যাডমিন ব্যবহারকারী তৈরি করুন
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@samiti.com',
            'phone' => '01829321686',
            'password' => Hash::make('password'), // পরীক্ষার জন্য সহজ পাসওয়ার্ড
        ]);
        $adminUser->assignRole($adminRole);
    }
}
