<?php

namespace Database\Seeders\Admin;

use App\Models\Permission;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Permission::updateOrCreate([
            'name' => 'admin_access',
            'user_id' => User::first()->id,
            'status_id' => Status::where('name', 'active')->first()->id ?? 0
        ]);
    }
}
