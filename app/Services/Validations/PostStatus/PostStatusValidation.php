<?php

namespace App\Services\Validations\PostStatus;

use Illuminate\Http\Request;

class PostStatusValidation implements PostStatusValidationInterface
{

    public function store(Request $request): mixed
    {

        $validateData = $request->validate(
            [
                'name' => 'required|string|unique:post_statuses,name,' . $request->id . ',id',
                'description' => 'required|string',
                'icon' => 'required|string',
                'class' => 'nullable|string',
            ]
        );

        return $validateData;
    }
}
