<?php

namespace App\Services\Validations\User;

use Illuminate\Http\Request;

class UserValidation implements UserValidationInterface
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
