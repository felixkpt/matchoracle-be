<?php

namespace App\Http\Controllers\Dashboard\Settings\RolePermissions\Permissions;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Support\Str;
use Felixkpt\Nestedroutes\GetNestedroutes;

class RoutesController extends Controller
{
    public Permission $permission;

    public function index()
    {
        $gen = new GetNestedroutes();
        $nestedRoutes = $gen->list();

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
