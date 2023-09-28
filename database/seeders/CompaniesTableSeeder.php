<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Faker\Factory as FakerFactory;

class CompaniesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create();

        // Set the total number you want to seed
        $totalRecords = 510;
        $batchSize = 100; // Set the desired batch size

        if (Company::count() >= $totalRecords) return;

        for ($i = 1; $i <= $totalRecords; $i++) {
            $email = $faker->unique()->safeEmail;

            Company::updateOrCreate(['email' => $email], [
                'name' => $faker->company,
                'email' => $email,
                'company_type_id' => CompanyType::inRandomOrder()->first()->id,
                'city' => $faker->city,
                'user_id' => User::inRandomOrder()->first()->id,
                // 80% 1
                'status' => rand(0, 10) <= 8 ? 1 : 0,
            ]);
        }
    }
}
