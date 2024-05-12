<?php

namespace App\Services\Validations\BettingStrategy;

use Illuminate\Http\Request;

interface BettingStrategyValidationInterface
{
    public function store(Request $request): mixed;
    
}
