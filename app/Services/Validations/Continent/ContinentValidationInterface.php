<?php

namespace App\Services\Validations\Continent;

use Illuminate\Http\Request;

interface ContinentValidationInterface
{
    public function store(Request $request): mixed;    
}
