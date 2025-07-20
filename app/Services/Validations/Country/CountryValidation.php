<?php

namespace App\Services\Validations\Country;

use App\Models\Country;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Http\Request;

class CountryValidation implements CountryValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(Request $request): mixed
    {
        $this->ensuresSlugIsUnique($request->name, Country::class);

        $validateData = $request->validate(
            [
                'name' => 'required|unique:countries,name,' . $request->id . ',id',
                'slug' => 'nullable|unique:countries,slug,' . $request->id . ',id',
                'code' => 'required|unique:countries,code,' . $request->id . ',id',
                'dial_code' => 'required',
                'continent_id' => 'required|exists:continents,id',
                'has_competitions' => 'required|integer',
                'flag' => $this->imageRules(),
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        return $validateData;
    }
}
