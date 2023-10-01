<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class PermissionRepository implements PermissionRepositoryInterface
{
    protected $leftTrim = 'api';
    protected $permissions = [];
    protected $folder_icons = [];
    protected $hidden_folders = [];

    protected $checked_permissions = [];

    public function index()
    {

        $permissions = Permission::whereNull('uri');

        if (request()->all == '1')
            return response(['results' => $permissions->get()]);

        $permissions = SearchRepo::of($permissions, ['name', 'id'])
            ->fillable(['name', 'guard_name'])
            ->addColumn('action', function ($permission) {
                return '
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="icon icon-list2 font-20"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item autotable-view" data-id="' . $permission->id . '" href="/admin/settings/role-permissions/view/' . $permission->id . '">View</a></li>
                <li><a class="dropdown-item autotable-edit" data-id="' . $permission->id . '" href="/admin/settings/role-permissions/permissions/view/' . $permission->id . '">Edit</a></li>
                <li><a class="dropdown-item autotable-status-update" data-id="' . $permission->id . '" href="/admin/settings/role-permissions/permissions/view/' . $permission->id . '/status-update">Status Update</a></li>
            </ul>
        </div>
        ';
            })->paginate();

        return response(['results' => $permissions]);
    }

    public function store(Request $request, $data)
    {
        $action = 'created';
        if ($request->id)
            $action = 'updated';

        $res = Permission::updateOrCreate(['id' => $request->id], $data);
        return response(['type' => 'success', 'message' => 'Permission ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json([
            'status' => true,
            'results' => $role,
        ]);
    }

    function getRolePermissions($id)
    {

        if ($id === 'all') {
            $permissions = Permission::whereNotNull('uri');
        } else {
            $permission = Role::findOrFail($id);
            $permissions = $permission->permissions();
        }

        $permissions = $permissions->get();

        if (request()->uri)
            $permissions = $permissions->pluck('uri');

        return response(['results' => $permissions]);
    }

    function statusUpdate($id)
    {
        $status_id = request()->status_id;
        Permission::find($id)->update(['status_id' => $status_id]);
        return response(['message' => "Status updated successfully."]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Role::find($id)->delete();
        return response(['message' => "Permission deleted successfully."]);
    }
}
