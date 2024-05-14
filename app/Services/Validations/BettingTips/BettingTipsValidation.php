<?php

namespace App\Services\Validations\BettingTips;

use Illuminate\Http\Request;

class BettingTipsValidation implements BettingTipsValidationInterface
{

    public function subscribe(Request $request): mixed
    {

        $validatedData = request()->validate(
            [
                'betting_strategy_id' => 'required|exists:betting_strategies,id',
                'payment_method' => 'required|string',
            ]
        );

        return $validatedData;
    }
}
