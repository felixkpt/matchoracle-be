<?php

namespace App\Services\Validations\Team\Coach;

use App\Models\GameSource;
use App\Models\Coach;
use App\Rules\ValidGameSourceUri;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;
use Illuminate\Support\Facades\Log;

class CoachValidation implements CoachValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, Coach::class);

        $validateData = $request->validate(
            [
                'first_name' => 'required|string',
                'last_name' => 'nullable|string',
                'date_of_birth' => 'required|string',
                'nationality_id' => 'required|exists:countries,id',
            ]
        );

        $validateData['name'] = trim($request->first_name . ' ' . $request->last_name);
        return $validateData;
    }
}
