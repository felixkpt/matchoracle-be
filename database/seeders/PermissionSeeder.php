<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions for admin
        $role_name = 'Super Admin';
        $permissions =  json_decode(file_get_contents(database_path('seeders/jsons/admin_permissions.json')), true);
        $this->attachAndSync($role_name, $permissions);

        // create permissions for guest
        $role_name = 'Guest';
        $permissions =  json_decode(file_get_contents(database_path('seeders/jsons/guest_permissions.json')), true);
        $this->attachAndSync($role_name, $permissions);
    }

    function attachAndSync($role_name, $permissions)
    {
        $attach = [];
        foreach ($permissions as $row) {
            $attach[] = Permission::updateOrCreate(
                ['name' => $row['name']],
                [
                    ...$row,
                    'status_id' => activeStatusId(),
                    'user_id' => User::first()->id ?? 0,
                ]
            )->id;
        }

        $role = Role::where('name', $role_name)->first();
        $role->permissions()->sync($attach);
    }
}
