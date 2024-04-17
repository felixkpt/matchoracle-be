<?php

namespace App\Services\Validations\GameScoreStatus;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameScoreStatusValidation implements GameScoreStatusValidationInterface
{

    public function store(Request $request): mixed
    {

        $validateData = $request->validate(
            [
                'name' => 'required|string|unique:game_score_statuses,name,' . $request->id . ',id',
                'description' => 'required|string',
                'icon' => 'required|string',
                'class' => 'nullable|string',
            ]
        );

        $validateData['slug'] = Str::slug($validateData['name']);

        return $validateData;
    }
}
