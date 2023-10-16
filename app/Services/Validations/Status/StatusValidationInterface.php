<?php

namespace App\Services\Validations\Status;

use Illuminate\Http\Request;

interface StatusValidationInterface
{
    public function store(Request $request): mixed;    
}
