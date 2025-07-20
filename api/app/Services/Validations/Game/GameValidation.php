<?php

namespace App\Services\Validations\Game;

use App\Services\Validations\ValidationFormatter;
use App\Rules\UniqueVote;

class GameValidation implements GameValidationInterface
{

    use ValidationFormatter;

    public function vote($id): mixed
    {

        $rules = [
            'vote' => ['required', 'string', new UniqueVote($id)],
            'type' => ['required', 'string'],
        ];

        // Validate the incoming request data
        $validatedData = request()->validate($rules);

        return $validatedData;
    }
}
