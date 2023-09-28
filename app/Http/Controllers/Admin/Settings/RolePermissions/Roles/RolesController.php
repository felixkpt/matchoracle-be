<?php

namespace App\Http\Controllers\Admin\Settings\RolePermissions\Roles;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{

    public function index()
    {

        $roles = Role::query();

        if (request()->all == '1')
            return response(['results' => $roles->get()]);

        $roles = SearchRepo::of($roles, ['name', 'id'])
            ->fillable(['name', 'guard_name'])
            ->addColumn('action', function ($role) {
                return '
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="icon icon-list2 font-20"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item autotable-navigate" href="/admin/settings/role-permissions/roles/view/' . $role->id . '">View</a></li>
                <li><a class="dropdown-item autotable-edit" data-id="' . $role->id . '" href="/admin/settings/role-permissions/roles/view/' . $role->id . '">Edit</a></li>
                <li><a class="dropdown-item autotable-status-update" data-id="' . $role->id . '" href="/admin/settings/role-permissions/roles/view/' . $role->id . '/status-update">' . ($role->status == 1 ? 'Deactivate' : 'Activate') . '</a></li>
            </ul>
        </div>
        ';
            })->paginate();

        return response(['results' => $roles]);
    }

    public function store(Request $request)
    {

        $data = $request->all();

        $validateUser = Validator::make(
            $data,
            [
                'name' => 'required|unique:roles,name,' . $request->id . ',id',
                'description' => 'nullable',
                'guard_name' => 'required'
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        $res = Role::updateOrCreate(['id' => $request->id], $data);
        return response(['type' => 'success', 'message' => 'Role ' . $action . ' successfully', 'results' => $res]);
    }


    function getUserRolesAndDirectPermissions()
    {
        $user = User::find(auth()->user()->id);
        $roles = $user->roles()->select('id', 'name')->get();

        $direct_permissions = $user->getPermissionNames();

        return response(['results' => compact('roles', 'direct_permissions')]);
    }

    public function destroy($permissiongroup_id)
    {
        $permissiongroup = Role::findOrFail($permissiongroup_id);
        if ($permissiongroup->is_default)
            return response(['type' => 'failure', 'message' => 'Default PermissionGroup cannot be deleted']);

        $permissiongroup->delete();
        return response(['type' => 'success', 'message' => 'PermissionGroup deleted successfully']);
    }
}
