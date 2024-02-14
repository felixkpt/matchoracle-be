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
            [
                'name' => 'Africa',
                'code' => 'AFR',
            ],
            [
                'name' => 'Antarctica',
                'code' => 'ANT',
            ],
            [
                'name' => 'Asia',
                'code' => 'ASI',
            ],
            [
                'name' => 'Europe',
                'code' => 'EU',
            ],
            [
                'name' => 'North & Central America',
                'code' => 'NAC',
            ],
            [
                'name' => 'Oceania',
                'code' => 'AOC',
            ],
            [
                'name' => 'South America',
                'code' => 'SOA',
            ],
            [
                'name' => 'World',
                'code' => 'INT',
            ],
        ];

        foreach ($continents as $country) {
            Continent::updateOrCreate(
                [
                    'name' => $country['name']
                ],
                [
                    'name' => $country['name'],
                    'slug' => Str::slug($country['name']),
                    'code' => $country['code'],
                    'flag' => 'assets/images/flags/png100px/' . Str::slug($country['code']) . '.png',
                    'status_id' => activeStatusId()
                ]
            );
        }
    }
}
