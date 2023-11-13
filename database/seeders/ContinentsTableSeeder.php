<?php

namespace Database\Seeders;

use App\Models\Continent;
use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContinentsTableSeeder extends Seeder
{
    public function run()
    {
        $continents = [
            ['name' => 'Africa', 'code' => 'AF'],
            ['name' => 'Antarctica', 'code' => 'AN'],
            ['name' => 'Asia', 'code' => 'AS'],
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'North America', 'code' => 'NA'],
            ['name' => 'Oceania', 'code' => 'OC'],
            ['name' => 'South America', 'code' => 'SA'],
        ];

        foreach ($continents as $country) {
            Continent::updateOrCreate(
                ['name' => $country['name']],
                [
                    'name' => $country['name'],
                    'slug' => Str::slug($country['name']),
                    'code' => $country['code'],
                    'status_id' => Status::where('name', 'active')->first()->id ?? 0
                ]
            );
        }
    }
}
