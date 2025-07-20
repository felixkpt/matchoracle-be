<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arr = [
            'name' => 'Demo User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin@example.com'),
            'email_verified_at' => Carbon::now(),
        ];

        User::updateOrCreate(['email' => $arr['email']], $arr);

        if (Schema::hasColumn('users', 'status_id')) {
            $arr['status_id'] = activeStatusId();
        }
    }
}
