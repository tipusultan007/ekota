<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'ধানমন্ডি শাখা', 'code' => 'DHA'],
            ['name' => 'গুলশান শাখা', 'code' => 'GUL'],
            ['name' => 'চট্টগ্রাম শাখা', 'code' => 'CTG'],
            ['name' => 'সিলেট শাখা', 'code' => 'SYL'],
            ['name' => 'খুলনা শাখা', 'code' => 'KHL'],
        ];

        foreach ($areas as $area) {
            Area::firstOrCreate(['code' => $area['code']], $area);
        }
    }
}
