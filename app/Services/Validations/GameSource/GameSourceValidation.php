<?php

namespace App\Services\Validations\GameSource;

use App\Rules\TopLevelDomainRule;
use Illuminate\Http\Request;

class GameSourceValidation implements GameSourceValidationInterface
{

    public function store(Request $request): mixed
    {

        $validateData = $request->validate(
            [
                'name' => 'required|string|unique:game_sources,name,' . $request->id . ',id',
                'url' => ['required', 'url', new TopLevelDomainRule, 'unique:game_sources,url,' . $request->id . ',id'],
                'description' => 'required|string|max:65535',
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        return $validateData;
    }
}
