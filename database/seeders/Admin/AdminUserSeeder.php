<?php

namespace Database\Seeders\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Demo User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin@example.com'),
            'email_verified_at' => Carbon::now(),
        ]);
    }
}
