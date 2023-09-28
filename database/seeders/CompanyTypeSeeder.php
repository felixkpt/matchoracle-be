<?php

namespace Database\Seeders;

use App\Models\CompanyType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companyTypes = [
            ['name' => 'Type 1'],
            ['name' => 'Type 2'],
            ['name' => 'Type 3'],
            ['name' => 'Type 4'],
            ['name' => 'Type 5'],
        ];

        // Seed the data into the database
        foreach ($companyTypes as $row) {
            CompanyType::updateOrCreate(
                ['name' => $row['name'],],
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
