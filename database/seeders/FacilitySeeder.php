<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facility;
use App\Models\User;

class FacilitySeeder extends Seeder
{
    public function run()
    {
        $facilities = [
            ['name' => 'Facility A'],
            ['name' => 'Facility B'],
            // Add more facilities as needed
        ];

        $user = User::inRandomOrder()->first();

        foreach ($facilities as $facility) {
            Facility::updateOrCreate(
                ['name' => $facility['name']],
                [
                    'user_id' => $user->id,
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
