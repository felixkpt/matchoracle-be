<?php

namespace App\Services\Validations\Team\CoachContract;

use App\Models\CoachContract;
use App\Services\Validations\CommonValidations;
use App\Services\Validations\ValidationFormatter;

class CoachContractValidation implements CoachContractValidationInterface
{
    use CommonValidations;
    use ValidationFormatter;

    public function store(): mixed
    {
        $request = request();

        $this->ensuresSlugIsUnique($request->name, CoachContract::class);

        $validateData = $request->validate(
            [
                'team_id' => 'required|exists:coaches,name',
                'coach_id' => 'required|exists:coaches,name',
                'start' => 'required|date',
                'until' => 'required|date',
            ]
        );

        return $validateData;
    }
}
