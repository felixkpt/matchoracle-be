<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\User;

class DepartmentSeeder extends Seeder
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
                'name' => 'Sales Department',
            ],
            [
                'name' => 'Marketing Department',
            ],
            [
                'name' => 'IT Department',
            ],
            [
                'name' => 'Human Resources Department',
            ],
            [
                'name' => 'Finance Department',
            ],
            [
                'name' => 'Customer Support Department',
            ],
        ];

        // Insert data into the database
        foreach ($data as $row) {
            Department::updateOrCreate(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
