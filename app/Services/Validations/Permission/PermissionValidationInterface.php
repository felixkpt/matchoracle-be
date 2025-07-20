<?php

namespace App\Services\Validations\Permission;

use Illuminate\Http\Request;

interface PermissionValidationInterface
{
    public function store(Request $request): mixed;
    
}
