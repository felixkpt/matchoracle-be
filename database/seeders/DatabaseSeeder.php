<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\Admin\AdminRoleSeeder;
use Database\Seeders\Admin\AdminUserSeeder;
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
            AdminRoleSeeder::class,
            PostStatusSeeder::class,
            PostCategoriesSeeder::class,
            PostTopicsSeeder::class,
            PermissionSeeder::class,
            ContinentsTableSeeder::class,
            CountriesSeeder::class,
            CompetitionSeeder::class,
            // AreaSeeder::class,
            // SeasonSeeder::class,
            // RoleSeeder::class,
            // UsersTableSeeder::class,
            // CompanyTypeSeeder::class,
            // CompaniesTableSeeder::class,
            // DepartmentSeeder::class,
            // IssueSourceSeeder::class,
            // IssueCategorySeeder::class,
            // DispositionSeeder::class,
            // SlaLevelSeeder::class,
            // QueueSeeder::class,
            // TicketStatusSeeder::class,

            // CustomersTableSeeder::class,
            // PostSeeder::class,
            // TicketSeeder::class,
        ];

        // shuffle($arr);

        $this->call($arr);

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
