<?php

namespace App\Services\Validations\Role;

use Illuminate\Http\Request;

class RoleValidation implements RoleValidationInterface
{

    public function store(Request $request): mixed
    {

        $data = $request->all();

        request()->validate(
            [
                'name' => 'required|unique:roles,name,' . $request->id . ',id',
                'description' => 'nullable',
                'guard_name' => 'required'
            ]
        );

        return $data;
    }

    public function storeRolePermissions(Request $request): void
    {
        $request->validate([
            'current_folder' => ['required', 'array'],
        ]);
    }

    function storeRoleMenuAndCleanPermissions(Request $request): void
    {
        $request->validate([
            'menu' => ['required', 'array'],
            'saved_folders' => ['required', 'array'],
            'all_folders' => ['required', 'array'],
        ]);
    }

    function addUser(): void
    {
        request()->validate([
            'user_id' => 'required|exists:users,id',
        ]);
    }
}
