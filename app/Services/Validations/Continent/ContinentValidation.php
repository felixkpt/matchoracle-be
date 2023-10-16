<?php

namespace App\Services\Validations\Continent;

use App\Models\Continent;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Http\Request;

class ContinentValidation implements ContinentValidationInterface
{

    use ValidationFormatter;

    public function store(Request $request): mixed
    {

        $this->ensuresSlugIsUnique($request->name, Continent::class);

        $validateData = $request->validate(
            [
                'name' => 'required|string|max:255|unique:continents,name,' . $request->id . ',id',
                'slug' => 'nullable|string|max:255|unique:continents,slug,' . $request->id . ',id',
                'code' => 'required|string|max:10',
                'image' => ['required', 'image'],
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        return $validateData;
    }
}
