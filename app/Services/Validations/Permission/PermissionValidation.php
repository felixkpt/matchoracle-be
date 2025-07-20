<?php

namespace App\Services\Validations\Permission;

use Illuminate\Http\Request;

class PermissionValidation implements PermissionValidationInterface
{

    public function store(Request $request): mixed
    {

        request()->validate(
            [
                'name' => 'required|unique:permissions,name,' . $request->id . ',id',
                'description' => 'nullable',
                'guard_name' => 'required'
            ]
        );

        return request()->all();
    }
}
