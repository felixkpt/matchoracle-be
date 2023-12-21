<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;

class AreaSeeder extends Seeder
{
    public function run()
    {
        Area::create([
            'name' => 'England',
            'slug' => 'england',
            'code' => 'ENG',
            'flag' => 'https://logos.football-data.org/770.svg',
        ]);
    }
}
