<?php

namespace App\Repositories\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RoleRepository implements RoleRepositoryInterface
{

    use CommonRepoActions;
    use RoleHelpers;

    private $checked_permissions = [];
    private $guestRoleId;

    function __construct(protected Role $model)
    {
        $this->guestRoleId = config('nestedroutes.guestRoleId') ?? 0;
    }

    public function index()
    {

        $roles = $this->model::query();

        if (request()->all == '1')
            return response(['results' => $roles->get()]);

        $uri =  '/dashboard/settings/role-permissions/roles/';
        $view = 'link';
        $edit = 'modal';
        $roles = SearchRepo::of($roles, ['name', 'id'])
            ->fillable(['name', 'guard_name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'Created_by')
            ->addColumn('Status', 'getStatus')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, $view, $edit))
            ->htmls(['Status'])
            ->paginate();

        return response(['results' => $roles]);
    }

    public function store(Request $request, $data)
    {

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        $res = $this->autoSave($data);
        return response(['type' => 'success', 'message' => 'Role ' . $action . ' successfully', 'results' => $res]);
    }

    function getUserRolesAndDirectPermissions()
    {
        $id = auth()->user()->id ?? 0;

        $roles = Role::query()->where('id', $this->guestRoleId)->get();
        $direct_permissions = null;
        if ($id) {

            $user = User::find($id);
            $roles = $user->roles()->select('id', 'name')->get();

            $direct_permissions = $user->getPermissionNames();
        }

        return response(['results' => compact('roles', 'direct_permissions')]);
    }

    public function show($id)
    {
        $role = $this->model::findOrFail($id);
        return response()->json([
            'status' => true,
            'results' => $role,
        ]);
    }

    /**
     * Store role permissions for a specific role.
     *
     * @param  Request $request
     * @param  int     $id
     * @return Response
     */
    function storeRolePermissions(Request $request, $id)
    {
        //  Log::info('RRR', Role::find($id)->permissions->select('name', 'guard_name', 'parent_folder', 'uri', 'title', 'icon', 'hidden', 'is_public', 'position')->toArray());

        // return response()->json(Permission::query()->select('name', 'guard_name', 'parent_folder', 'uri', 'title', 'icon', 'hidden', 'is_public', 'position')->get()->toArray());

        $start = Carbon::now();

        // Get the current folder from the request
        $current_folder = $request->current_folder;
        $parent_folder = $current_folder['folder'];

        // Find the role by ID along with its permissions, excluding those from the current folder
        $role = $this->model::find($id);

        // If the role doesn't exist, return a 404 response
        if (!$role) {
            return response(['message' => 'Role not found', 'status' => false], 404);
        }

        // Get the guard name of the role
        $guard_name = $role->guard_name;

        // Extract and save permissions for the current folder
        $this->extractAndSavePermissions($parent_folder, $current_folder, $guard_name);

        sleep(2);

        try {
            DB::beginTransaction();

            $existing = $this->model::with(['permissions' => function ($q) use ($parent_folder) {
                $q->where('parent_folder', '=', $parent_folder);
            }])->find($id)->permissions->pluck('id')->toArray();

            // Log::info("Existing for folder:", ['parent_folder' => $parent_folder, 'permissions' => $existing]);

            $role->permissions()->detach($existing);

            $attach = array_values(array_unique($this->checked_permissions));

            // Sync role with permissions
            $role->permissions()->attach($attach);

            DB::commit();

            return response([
                'message' => "Permissions for <b>{$parent_folder}</b> have been updated successfully",
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response([
                'message' => $e->getMessage(),
            ]);
        }
    }

    function storeRoleMenuAndCleanPermissions(Request $request, $id)
    {

        $menu = $request->menu;
        $saved_folders = $request->saved_folders;
        $all_folders = $request->all_folders;

        $role = $this->model::find($id);
        if (!$role) return response(['message' => 'Role not found', 'status' => false,], 404);

        // 1. Remove permissions for parent folders not in the list of saved folders (probably the current role does not need the folders anymore)
        $permissionsToRemove = $role->permissions()->whereNotIn('parent_folder', $saved_folders)->pluck('id')->toArray();
        // Log::info('permissionsToRemove', ['permissionsToRemove' => $permissionsToRemove]);
        $role->permissions()->detach($permissionsToRemove);

        // 2. Delete permissions for parent folders not in all_folders (probably the folders were renamed or deleted)
        $permissionsToDelete = Permission::whereNotIn('parent_folder', $all_folders);
        $permissionsToDelete->delete();

        try {
            $this->saveJson($role, $menu);

            return response([
                'message' => 'Menu saved!',
            ]);
        } catch (Exception $e) {

            return response([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function getRoleMenu($id)
    {
        // a user can have more than 1 roles
        if ($id == 0) {
            $id = $this->guestRoleId;
        }

        $role = $this->model::find($id);
        if (!$role) {
            return response()->json(['message' => 'Role not found!!'], 404);
        }

        // save user's default_role_id
        $user = User::find(auth()->id());
        if ($user) {
            $user->default_role_id = $id;
            $user->save();
        }

        // Get JSON from storage
        $filePath = '/system/roles/' . Str::slug($role->name) . '_menu.json';

        if (!Storage::exists($filePath)) {
            return response()->json(['message' => 'Role ' . $role->name . ' permissions file not found'], 404);
        }

        $jsonContent = file_get_contents(Storage::path($filePath));

        return response()->json(['results' => ['roles' => $role, 'menu' => json_decode($jsonContent), 'expanded_root_folders' => [config('nestedroutes.folder'), 'dashboard']]]);
    }

    function getRoleRoutePermissions($id)
    {
        $user = User::find(auth()->user()->id ?? 0);

        $role = $this->model::findOrFail($id);
        if ((!$user && $id != $this->guestRoleId) || ($user && !$user->hasRole($role))) {
            return response(['message' => "User doesnt have the {$role->id} role."], 404);
        }

        // Get all permissions associated with user's roles
        $route_permissions = $role->permissions->pluck('uri');

        return response(['results' => $route_permissions]);
    }

    function addUser($id)
    {

        $role = $this->model::find($id);
        if (!$role) return response(['message' => 'Role not found', 'status' => false,], 404);

        $user = User::find(request()->user_id);
        $user->assignRole($role);

        return response(['results' => $user, 'message' => "{$user->name} added to role {$role->name}"]);
    }
}
