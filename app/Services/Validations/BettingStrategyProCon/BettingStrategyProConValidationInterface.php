<?php

namespace App\Services\Validations\BettingStrategyProCon;

use Illuminate\Http\Request;

interface BettingStrategyProConValidationInterface
{
    public function store(Request $request): mixed;
    
}
