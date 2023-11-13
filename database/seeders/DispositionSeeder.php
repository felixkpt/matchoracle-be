<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disposition;
use App\Models\User;

class DispositionSeeder extends Seeder
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
                'name' => 'Bought airtime, no credit',
                'priority_id' => 1,
                'fcr' => 0,
            ],
            [
                'name' => 'Pending transaction on backoffice',
                'priority_id' => 1,
                'fcr' => 0,
            ],
            [
                'name' => 'Junk email/unsolicited email',
                'priority_id' => 3,
                'fcr' => 1,
            ],

            [
                'name' => 'Issue with online recharge',
                'priority_id' => 2,
                'fcr' => 0,
            ],
            [
                'name' => 'Service not available in area',
                'priority_id' => 1,
                'fcr' => 1,
            ],
            [
                'name' => 'Results Collection',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
            [
                'name' => 'Medical Report',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
            [
                'name' => 'Homebased Care Services',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
            [
                'name' => 'Appointment Booking',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
            [
                'name' => 'Appointment Rescheduling',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
            [
                'name' => 'Appointment Cancellation',
                'priority_id' => NULL,
                'fcr' => 1,
            ],
        ];

        // Insert data into the database
        foreach ($data as $row) {
            Disposition::updateOrCreate(['name' => $row['name']], [
                'name' => $row['name'],
                'priority_id' => $row['priority_id'],
                'fcr' => $row['fcr'],
                'user_id' => User::inRandomOrder()->first()->id,
                // 80% 1
                'status' => rand(0, 10) <= 8 ? 1 : 0,
            ]);
        }
    }
}
