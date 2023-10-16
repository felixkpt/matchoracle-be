<?php

namespace App\Services\Validations\Country;

use Illuminate\Http\Request;

interface CountryValidationInterface
{
    public function store(Request $request): mixed;    
}
