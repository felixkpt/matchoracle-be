<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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

        // create permissions

        $permissions = [
            [
                "name" => "posts",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts",
                "title" => "posts",
                "icon" => "pixelarticons:article-multiple",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings",
                "title" => "settings",
                "icon" => "mdi:settings-outline",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin",
                "guard_name" => "api",
                "parent_folder" => "admin",
                "uri" => "admin",
                "title" => "admin",
                "icon" => "file-icons:dashboard",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts@GET|@HEAD",
                "title" => "Posts  List",
                "icon" => "fa6-solid:signs-post",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions",
                "title" => "settings/role-permissions",
                "icon" => "fa-solid:users-cog",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "admin",
                "uri" => "admin@GET|@HEAD",
                "title" => "Admin Dash",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories",
                "title" => "posts/categories",
                "icon" => "iconamoon:category-bold",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles",
                "title" => "settings/role-permissions/roles",
                "icon" => "tdesign:user-list",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories@GET|@HEAD",
                "title" => "Categories  List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles@GET|@HEAD",
                "title" => "List  Roles",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.slug.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories/{slug}@GET|@HEAD",
                "title" => "Show  Category",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles@POST",
                "title" => "Add/ Save  Role",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.slug.topics.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories/{slug}/topics@GET|@HEAD",
                "title" => "List  Cat  Topics",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.get.user.roles.and.direct.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/get-user-roles-and-direct-permissions@GET|@HEAD",
                "title" => "Get User Roles And Direct Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.topics",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories/topics",
                "title" => "posts/categories/topics",
                "icon" => "icon-park-outline:topic",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view",
                "title" => "settings/role-permissions/roles/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.topics.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories/topics@GET|@HEAD",
                "title" => "Topics  List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.save.permissions.at.get.at.head.at.post.at.put.at.patch.at.delete.at.options",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/save-permissions@GET|@HEAD|@POST|@PUT|@PATCH|@DELETE|@OPTIONS",
                "title" => "Save  Role  Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.categories.topics.detail.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/categories/topics/detail/{id}@GET|@HEAD",
                "title" => "Show Topic",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.save.menu.and.clean.permissions.at.get.at.head.at.post.at.put.at.patch.at.delete.at.options",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/save-menu-and-clean-permissions@GET|@HEAD|@POST|@PUT|@PATCH|@DELETE|@OPTIONS",
                "title" => "Store Role Menu And Clean Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.view",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/view",
                "title" => "posts/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.get.role.menu.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/get-role-menu@GET|@HEAD",
                "title" => "Get Role Menu",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.get.user.route.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/get-user-route-permissions@GET|@HEAD",
                "title" => "Get User Route Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "posts.view.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "posts/view/{id}@GET|@HEAD",
                "title" => "Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.add.user.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/add-user@POST",
                "title" => "Add User",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}@GET|@HEAD",
                "title" => "Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}@PUT",
                "title" => "Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.roles.view.id.status.update.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/roles/view/{id}/status-update@PATCH",
                "title" => "Status Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users",
                "title" => "settings/users",
                "icon" => "mdi:users-add",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users/view",
                "title" => "settings/users/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users.view.update.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users/view/update@POST",
                "title" => "User Profile",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users.view.profile.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users/view/profile@GET|@HEAD",
                "title" => "Profile Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users.view.profile.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users/view/profile@PATCH",
                "title" => "Profile Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.users.view.update.self.password.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/users/view/update-self-password@PATCH",
                "title" => "Update Self Password",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions",
                "title" => "settings/role-permissions/permissions",
                "icon" => "fluent-mdl2:permissions",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.get.role.permissions.roleid.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions/get-role-permissions/{role_id}@GET|@HEAD",
                "title" => "Get Role Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions@GET|@HEAD",
                "title" => "List  Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions@POST",
                "title" => "Store",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.routes.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions/routes@GET|@HEAD",
                "title" => "List  Routes",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.routes.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions/routes@POST",
                "title" => "Store  Route",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions/view",
                "title" => "settings/role-permissions/permissions/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "settings.role.permissions.permissions.view.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "settings/role-permissions/permissions/view/{id}@PUT",
                "title" => "Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ]
        ];

        $attach = [];
        foreach ($permissions as $row) {
            $attach[] = Permission::updateOrCreate(
                ['name' => $row['name']],
                $row
            )->id;
        }

        $role = Role::where('name', 'Super Admin')->first();
        $role->permissions()->sync($attach);
    }
}
