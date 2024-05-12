<?php

namespace App\Services\Validations\BettingStrategy;

use Illuminate\Http\Request;

class BettingStrategyValidation implements BettingStrategyValidationInterface
{

    public function store(Request $request): mixed
    {

        $validatedData = request()->validate(
            [
                'name' => 'required|unique:betting_strategies,name,' . $request->id . ',id',
                'slogan' => 'required|string',
                'description' => 'nullable',
                'amount' => 'required|numeric',
                'position' => 'numeric',
            ]
        );

        return $validatedData;
    }
}
