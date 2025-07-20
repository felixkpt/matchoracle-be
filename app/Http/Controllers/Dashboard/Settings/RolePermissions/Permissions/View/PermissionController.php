<?php

namespace App\Http\Controllers\Dashboard\Settings\RolePermissions\Permissions\View;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Services\Validations\Permission\PermissionValidationInterface;

class PermissionController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private PermissionRepositoryInterface $permissionRepositoryInterface,
        private PermissionValidationInterface $permissionValidationInterface
    ) {
        $this->repo = $permissionRepositoryInterface;
    }
}
