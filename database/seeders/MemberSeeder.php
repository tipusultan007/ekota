<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\Member;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $areas = Area::all();

        if ($areas->isEmpty()) {
            $this->command->info('No areas found. Please run AreaSeeder first.');
            return;
        }

        // প্রতিটি এলাকায় ২০ জন করে সদস্য তৈরি করুন
        $areas->each(function ($area) {
            Member::factory()->count(20)->create([
                'area_id' => $area->id,
            ]);
        });

        $this->command->info('Created 100 test members across 5 areas.');
    }
}
