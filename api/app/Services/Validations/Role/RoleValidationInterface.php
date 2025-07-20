<?php

namespace App\Services\Validations\Role;

use Illuminate\Http\Request;

interface RoleValidationInterface
{
    public function store(Request $request): mixed;
    
    public function storeRolePermissions(Request $request): void;

    function storeRoleMenuAndCleanPermissions(Request $request): void;

    function addUser(): void;
}
