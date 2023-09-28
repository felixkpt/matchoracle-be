<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SlaLevel;
use App\Models\User;

class SlaLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'Level 1',
            ],
            [
                'name' => 'Level 2',
            ],
            [
                'name' => 'Level 3',
            ],
            [
                'name' => 'Level 4',
            ],
        ];

        // Insert data into the database
        foreach ($data as $row) {
            SlaLevel::updateOrCreate(
                [
                    'name' => $row['name'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ],
            );
        }
    }
}
