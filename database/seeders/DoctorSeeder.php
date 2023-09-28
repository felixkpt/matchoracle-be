<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Str;


class DoctorSeeder extends Seeder
{
    public function run()
    {

        $faker = Faker::create();

        $start = 0;
        $end = 30;

        for ($i = $start; $i <= $end; $i++) {

            $name = $faker->name;
            $email = $faker->unique()->safeEmail;

            Doctor::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'phone' => $faker->optional()->numerify('+254##########'),
                    'email' => $email,
                    'speciality_id' => Specialty::inRandomOrder()->first()->id,
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                    'user_id' => User::inRandomOrder()->first()->id,
                ]
            );
        }
    }
}
