<?php

namespace App\Http\Controllers\Admin\Settings\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\SearchRepo;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;

class UsersController extends Controller
{
    public function index()
    {

        $users = User::with(['roles'])->when(request()->role_id, function ($q) {
            if (request()->has('negate')) {
                $q->whereDoesntHave('roles', function ($q) {
                    $q->where('roles.id', request()->role_id);
                });
            } else {
                $q->whereHas('roles', function ($q) {
                    $q->where('roles.id', request()->role_id);
                });
            }
        });

        $users = SearchRepo::of($users, ['name', 'id'])
            ->addColumn('Roles', function ($user) {
                return implode(', ', $user->roles()->get()->pluck('name')->toArray());
            })
            ->addFillable('password_confirmation', 'avatar')
            ->addFillable('roles_multilist')
            ->addFillable('direct_permissions_multilist')
            ->addFillable('two_factor_enabled', 'theme', ['input' => 'input', 'type' => 'checkbox'])
            ->addFillable('allowed_session_no', 'theme', ['input' => 'input', 'type' => 'number', 'min' => 1, 'max' => 10])
            ->addColumn('action', function ($user) {
                return '
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="icon icon-list2 font-20"></i>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item autotable-navigate" href="/admin/settings/users/view/' . $user->id . '">View</a></li>
            '
                    .
                    (checkPermission('users', 'post') ?
                        '<li><a class="dropdown-item autotable-edit" data-id="' . $user->id . '" href="/admin/settings/users/view/' . $user->id . '/edit">Edit</a></li>'
                        :
                        '')
                    .
                    '<li><a class="dropdown-item autotable-status-update" data-id="' . $user->id . '" href="/admin/settings/users/view/' . $user->id . '/status-update">' . ($user->status == 1 ? 'Deactivate' : 'Activate') . '</a></li>
            <li><a class="dropdown-item autotable-delete" data-id="' . $user->id . '" href="/admin/settings/users/view/' . $user->id . '">Delete</a></li>
        </ul>
    </div>
    ';
            })->paginate();

        return response(['results' => $users, 'status' => true]);
    }

    public function create()
    {
        return response(['status' => true, 'results' => null]);
    }

    public function store(Request $request)
    {

        Log::info('in', [$request->all()]);

        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email,' . $request->id,
            'phone' => 'nullable|min:4|max:10|unique:users,phone,' . $request->id,
            'roles_list' => 'required|array|min:1|max:10',
            'direct_permissions_list' => 'nullable|array',
        ];

        if (!$request->id) {
            $rules = array_merge($rules, [
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required',
            ]);
        }

        $request->validate($rules);

        $request->merge([
            'email' => strtolower($request->email),
            'two_factor_enabled' => !!$request->two_factor_enabled
        ]);

        if (!$request->id || $request->refresh_api_token) {
            $request->merge([
                'api_token' => Str::random(20),
            ]);
        }

        $data = $request->all();

        $data['password'] = bcrypt($request->input('password'));

        $user = User::updateOrCreate(['id' => $request->id], $data);

        if (!$user->default_role_id) {
            $user->default_role_id = $request->roles_list[0];
            $user->save();
        }

        if ($request->roles_list) {
            $roles = Role::whereIn('id', $request->roles_list)->get();
            $user->syncRoles($roles);
        }

        if ($request->direct_permissions_list) {
            $permissions = Permission::whereIn('id', $request->direct_permissions_list)->get();
            $user->syncPermissions($permissions);
        }

        return response(['message' => 'User ' . ($request->id ? 'updated' : 'created') . ' successfully.']);
    }

    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return response(['status' => true, 'results' => $user]);
    }

    // public function update(Request $request, User $user)
    // {

    //     $request->merge(['roles' => json_decode(request()->roles)]);

    //     $request->validate([
    //         'name' => 'required',
    //         'email' => 'required|unique:users,email,' . $user->id,
    //         'password' => 'nullable|min:8',
    //         'roles' => 'required|array',
    //     ]);

    //     $user->update([
    //         'name' => $request->input('name'),
    //         'email' => $request->input('email'),
    //         'password' => $request->input('password') ? bcrypt($request->input('password')) : $user->password,
    //     ]);

    //     if ($request->roles) {

    //         $roles = Role::whereIn('id', $request->input('roles'))->get();
    //         $user->syncRoles($roles);
    //     }

    //     // Additional logic if needed

    //     return redirect()->route('users.index')
    //         ->with('success', 'User updated successfully.');
    // }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
