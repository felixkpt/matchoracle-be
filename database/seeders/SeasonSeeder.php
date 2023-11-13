<?php

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;
use App\Models\Season;

class SeasonSeeder extends Seeder
{
    public function run()
    {
        // For the current season


        $items = [
            [
                'start_date' => '2021-08-13',
                'end_date' => '2022-05-22',
                'current_matchday' => 37,
                'competition_id' => Competition::first()->id,
                'is_current' => true
            ],
            [
                'start_date' => '2014-08-16',
                'end_date' => '2015-05-24',
                'current_matchday' => null,
                'competition_id' => Competition::first()->id,
                'updated_at' => now(),
                'created_at' => now(),
            ],
            [
                'start_date' => '2012-08-17',
                'end_date' => '2014-05-11',
                'current_matchday' => null,
                'competition_id' => Competition::first()->id,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        ];

        foreach ($items as $item) {
            Season::create($item);
        }

    }
}
