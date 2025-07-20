<?php

namespace App\Services\Validations\Team\Address;

use App\Models\Address;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;

class AddressValidation implements AddressValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, Address::class);

        $validateData = $request->validate(
            [
                'name' => 'required|unique:addresses,name,' . $request->id . ',id',
            ]
        );

        return $validateData;
    }

}
