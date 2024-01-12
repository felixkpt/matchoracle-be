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

        // create permissions

        $permissions =  [
            [
                "name" => "admin.posts",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts",
                "title" => "admin/posts",
                "icon" => "pixelarticons:article-multiple",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings",
                "title" => "admin/settings",
                "icon" => "mdi:settings-outline",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.admin",
                "guard_name" => "api",
                "parent_folder" => "admin",
                "uri" => "admin/admin",
                "title" => "admin/admin",
                "icon" => "file-icons:dashboard",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts@GET|@HEAD",
                "title" => "admin/Posts  List",
                "icon" => "fa6-solid:signs-post",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions",
                "title" => "admin/settings/role-permissions",
                "icon" => "fa-solid:users-cog",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.admin.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "admin",
                "uri" => "admin/admin@GET|@HEAD",
                "title" => "admin/Admin Dash",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories",
                "title" => "admin/posts/categories",
                "icon" => "iconamoon:category-bold",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles",
                "title" => "admin/settings/role-permissions/roles",
                "icon" => "tdesign:user-list",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories@GET|@HEAD",
                "title" => "admin/Categories  List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles@GET|@HEAD",
                "title" => "admin/List  Roles",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.slug.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories/{slug}@GET|@HEAD",
                "title" => "admin/Show  Category",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles@POST",
                "title" => "admin/Add/ Save  Role",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.slug.topics.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories/{slug}/topics@GET|@HEAD",
                "title" => "admin/List  Cat  Topics",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.get.user.roles.and.direct.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/get-user-roles-and-direct-permissions@GET|@HEAD",
                "title" => "admin/Get User Roles And Direct Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.topics",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories/topics",
                "title" => "admin/posts/categories/topics",
                "icon" => "icon-park-outline:topic",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view",
                "title" => "admin/settings/role-permissions/roles/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.topics.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories/topics@GET|@HEAD",
                "title" => "admin/Topics  List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.save.permissions.at.get.at.head.at.post.at.put.at.patch.at.delete.at.options",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/save-permissions@GET|@HEAD|@POST|@PUT|@PATCH|@DELETE|@OPTIONS",
                "title" => "admin/Save  Role  Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.categories.topics.detail.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/categories/topics/detail/{id}@GET|@HEAD",
                "title" => "admin/Show Topic",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.save.menu.and.clean.permissions.at.get.at.head.at.post.at.put.at.patch.at.delete.at.options",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/save-menu-and-clean-permissions@GET|@HEAD|@POST|@PUT|@PATCH|@DELETE|@OPTIONS",
                "title" => "admin/Store Role Menu And Clean Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.view",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/view",
                "title" => "admin/posts/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.get.role.menu.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/get-role-menu@GET|@HEAD",
                "title" => "admin/Get Role Menu",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.get.user.route.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/get-user-route-permissions@GET|@HEAD",
                "title" => "admin/Get User Route Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.posts.view.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "posts",
                "uri" => "admin/posts/view/{id}@GET|@HEAD",
                "title" => "admin/Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.add.user.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/add-user@POST",
                "title" => "admin/Add User",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}@GET|@HEAD",
                "title" => "admin/Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}@PUT",
                "title" => "admin/Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.roles.view.id.status.update.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/roles/view/{id}/update-status@PATCH",
                "title" => "admin/Update Status",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users",
                "title" => "admin/settings/users",
                "icon" => "mdi:users-add",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users/view",
                "title" => "admin/settings/users/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users.view.update.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users/view/update@POST",
                "title" => "admin/User Profile",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users.view.profile.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users/view/profile@GET|@HEAD",
                "title" => "admin/Profile Show",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users.view.profile.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users/view/profile@PATCH",
                "title" => "admin/Profile Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.users.view.update.self.password.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/users/view/update-self-password@PATCH",
                "title" => "admin/Update Self Password",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions",
                "title" => "admin/settings/role-permissions/permissions",
                "icon" => "fluent-mdl2:permissions",
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.get.role.permissions.roleid.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/get-role-permissions/{role_id}@GET|@HEAD",
                "title" => "admin/Get Role Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions@GET|@HEAD",
                "title" => "admin/List  Permissions",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions@POST",
                "title" => "admin/Store",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.routes.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/routes@GET|@HEAD",
                "title" => "admin/List  Routes",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.routes.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/routes@POST",
                "title" => "admin/Store  Route",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.view",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/view",
                "title" => "admin/settings/role-permissions/permissions/view",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.view.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/view/{id}@PUT",
                "title" => "admin/Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.role.permissions.permissions.view.id.status.update.at.patch",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/role-permissions/permissions/view/{id}/update-status@PATCH",
                "title" => "admin/Update Status",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists",
                "title" => "admin/settings/picklists",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses",
                "title" => "admin/settings/picklists/statuses",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.default",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/default",
                "title" => "admin/settings/picklists/statuses/default",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.default.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/default@GET|@HEAD",
                "title" => "admin/List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.default.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/default@POST",
                "title" => "admin/Store",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.default.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/default/{id}@PUT",
                "title" => "admin/Update",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/post",
                "title" => "admin/settings/picklists/statuses/post",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.post.at.get.at.head",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/post@GET|@HEAD",
                "title" => "admin/List",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.post.at.post",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/post@POST",
                "title" => "admin/Store  Post Status",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ],
            [
                "name" => "admin.settings.picklists.statuses.post.id.at.put",
                "guard_name" => "api",
                "parent_folder" => "settings",
                "uri" => "admin/settings/picklists/statuses/post/{id}@PUT",
                "title" => "admin/Update  Post Status",
                "icon" => null,
                "hidden" => 0,
                "position" => 999999
            ]
        ];;


        $attach = [];
        foreach ($permissions as $row) {
            $attach[] = Permission::updateOrCreate(
                ['name' => $row['name']],
                [
                    ...$row,
                    'status_id' => Status::where('name', 'active')->first()->id ?? 0,
                    'user_id' => User::first()->id ?? 0,
                ]
            )->id;
        }

        $role = Role::where('name', 'Super Admin')->first();
        $role->permissions()->sync($attach);
    }
}
