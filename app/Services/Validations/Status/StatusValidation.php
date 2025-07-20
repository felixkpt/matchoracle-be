<?php

namespace App\Services\Validations\Status;

use Illuminate\Http\Request;

class StatusValidation implements StatusValidationInterface
{

    public function store(Request $request): mixed
    {

        $validateData = $request->validate(
            [
                'name' => 'required|string|unique:statuses,name,' . $request->id . ',id',
                'description' => 'required|string',
                'icon' => 'required|string',
                'class' => 'nullable|string',
            ]
        );

        return $validateData;
    }
}
