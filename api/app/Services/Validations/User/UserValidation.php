<?php

namespace App\Services\Validations\User;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserValidation implements UserValidationInterface
{

    public function store(Request $request): mixed
    {

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

        return request()->all();
    }
}
