<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class PermissionRepository implements PermissionRepositoryInterface
{

    use CommonRepoActions;

    private $checked_permissions = [];

    protected $leftTrim = 'api';
    protected $permissions = [];
    protected $folder_icons = [];
    protected $hidden_folders = [];

    function __construct(protected Permission $model)
    {
    }

    public function index()
    {

        $permissions = $this->model::whereNull('uri');

        $uri = '/dashboard/settings/role-permissions/permissions/';
        $permissions = SearchRepo::of($permissions, ['name', 'id'])
            ->fillable(['name', 'guard_name'])
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Created_at', 'Created_at')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->paginate();

        return response(['results' => $permissions]);
    }

    public function store(Request $request, $data)
    {
        $action = 'created';
        if ($request->id)
            $action = 'updated';

        $res = $this->autoSave($data);
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
            $permissions = $this->model::whereNotNull('uri');
        } else {
            $permission = Role::findOrFail($id);
            $permissions = $permission->permissions();
        }

        $permissions = $permissions->get();

        if (request()->uri)
            $permissions = $permissions->pluck('uri');

        return response(['results' => $permissions]);
    }

    function updateStatus($id)
    {
        $status_id = request()->status_id;
        $this->model::find($id)->update(['status_id' => $status_id]);
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
