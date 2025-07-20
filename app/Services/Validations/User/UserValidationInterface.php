<?php

namespace App\Services\Validations\User;

use Illuminate\Http\Request;

interface UserValidationInterface
{
    public function store(Request $request): mixed;
    
}
