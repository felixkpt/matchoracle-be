<?php

namespace App\Services\Validations\Team\Venue;

use App\Models\Venue;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;

class VenueValidation implements VenueValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, Venue::class);

        $validateData = $request->validate(
            [
                'name' => 'required|unique:venues,name,' . $request->id . ',id',
                'slug' => 'nullable|unique:venues,slug,' . $request->id . ',id',
                'description' => 'nullable|string',
                'website' => 'nullable|url',
                'priority_number' => 'nullable|integer|between:1,99999999',
            ]
        );

        return $validateData;
    }
}
