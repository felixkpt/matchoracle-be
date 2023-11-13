<?php

namespace App\Http\Controllers\Admin\Settings\RolePermissions\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\NestedRoutes\GetNestedRoutes;
use Illuminate\Support\Str;
use App\Models\Role;

class RoutesController extends Controller
{
    public Permission $permission;

    public function index()
    {
        $prefix = 'admin';
        $gen = new GetNestedRoutes($prefix, '');
        $nestedRoutes = $gen->list($prefix);


        // $role = Role::find(1);

        // $existing = Role::with(['permissions' => function ($q) {
        //     $q->where('name', 'not like', 'admin%');
        // }])->find($role->id);

        // dd($existing->permissions->pluck('id'));


        return response(['results' => $nestedRoutes]);
    }

    function store()
    {

        if (request()->checked)
            foreach (request()->checked as $uri) {
                $slug = Str::slug(Str::replace('/', ' ', Str::before($uri, '@')), '.');

                Permission::updateOrCreate(['name' => $slug], ['name' => $slug, 'uri' => $uri, 'guard_name' => 'api', 'user_id' => auth()->id()]);
            }

        return response([
            'status' => 'success',
            'message' => 'Persssions saved!',
            'results' => Permission::whereNotNull('uri')->get()
        ]);
    }
}
