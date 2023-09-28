<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketStatus;
use App\Models\User;

class TicketStatusSeeder extends Seeder
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
                'name' => 'Open',
            ],
            [
                'name' => 'Resolved',
            ],
            [
                'name' => 'Closed',
            ],
            [
                'name' => 'Pending Internally',
            ],
            [
                'name' => 'Pending Externally',
            ],
            [
                'name' => 'Re-Opened',
            ],
            [
                'name' => 'In Progress',
            ],
            [
                'name' => 'Test status2',
            ],
        ];

        // Insert data into the database
        foreach ($data as $row) {
            TicketStatus::updateOrCreate(
                [
                    'name' => $row['name'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ],
            );;
        }
    }
}
