<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Countries\CountriesSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $arr = [

            StatusSeeder::class,
            AdminUserSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,

            GameScoreStatusSeeder::class,
            ContinentsTableSeeder::class,
            CountriesSeeder::class,
            CompetitionSeeder::class,
            AppSettingSeeder::class,
        ];

        $this->call($arr);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
