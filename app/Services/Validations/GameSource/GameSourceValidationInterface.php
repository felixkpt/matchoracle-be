<?php

namespace App\Services\Validations\GameSource;

use Illuminate\Http\Request;

interface GameSourceValidationInterface
{
    public function store(Request $request): mixed;    
}
