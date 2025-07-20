<?php

namespace App\Services\Validations\GameScoreStatus;

use Illuminate\Http\Request;

interface GameScoreStatusValidationInterface
{
    public function store(Request $request): mixed;    
}
