<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;
use App\Models\User;

class SpecialtySeeder extends Seeder
{
    public function run()
    {
        $specialties = [
            ['name' => 'Neurologist'],
            ['name' => 'Psychologist'],
            ['name' => 'Cardiologist'],
            ['name' => 'Dermatologist'],
            ['name' => 'Orthopedic Surgeon'],
            ['name' => 'Oncologist'],
            ['name' => 'Pediatrician'],
            ['name' => 'Gastroenterologist'],
            ['name' => 'Endocrinologist'],
            ['name' => 'Ophthalmologist'],
            ['name' => 'ENT Specialist'],
            ['name' => 'Urologist'],
            ['name' => 'Gynecologist'],
            ['name' => 'Radiologist'],
            ['name' => 'Dentist'],
            ['name' => 'Allergist'],
            ['name' => 'Rheumatologist'],
            ['name' => 'Nephrologist'],
            ['name' => 'Plastic Surgeon'],
            ['name' => 'Anesthesiologist'],
        ];

        $user = User::inRandomOrder()->first();

        foreach ($specialties as $specialty) {
            Specialty::updateOrCreate(
                ['name' => $specialty['name']],
                [
                    'user_id' => $user->id,
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
