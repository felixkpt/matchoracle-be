<?php

namespace Database\Seeders\Admin;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        $role_counts = Role::count();

        $arr = [
            [
                'name' => 'Super Admin',
                'guard_name' => 'api',
            ],
            [
                'name' => 'Guest',
                'guard_name' => 'api',
            ]
        ];

        foreach ($arr as $item) {

            if (Schema::hasColumn('roles', 'user_id')) {
                $item['user_id'] = User::first()->id;
            }
            if (Schema::hasColumn('roles', 'status_id')) {
                $item['status_id'] = activeStatusId();
            }

            $role = Role::updateOrCreate(['name' => $item['name']], $item);
        }

        try {
            $role = Role::where('name', 'Super Admin')->first();
            $user = User::first();
            $user->assignRole($role);
            if (Schema::hasColumn('users', 'default_role_id')) {
                $user->default_role_id = $role->id;
                $user->save();
            }
        } catch (Exception $e) {
            dd('User assignRole error: ', $e->getMessage() . '. Also, please ensure config > auth.php > guards > api key exists.');
        }

        if ($role_counts === 0) {

            try {
                // Delete the entire directory along with its contents
                Storage::deleteDirectory('system/roles');

                // Success message
                echo "The roles menu directory 'storage/app/system/roles/' and its contents have been deleted.\n";
            } catch (Exception $e) {
                // Handle any errors that may occur during the deletion process
                echo "An error occurred: " . $e->getMessage() . ".\n";
            }
        }
    }
}
