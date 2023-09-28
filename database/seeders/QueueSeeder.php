<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Queue;
use App\Models\User;

class QueueSeeder extends Seeder
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
                'name' => 'Demo',
                'email' => 'demo@swiftcrm.com',
            ],
        ];

        // Insert data into the database
        foreach ($data as $row) {
            Queue::updateOrCreate(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
