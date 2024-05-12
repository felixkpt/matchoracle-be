<?php

namespace App\Http\Controllers\Dashboard\Settings\RolePermissions\Roles;

use App\Http\Controllers\Controller;
use App\Models\Sanctum\PersonalAccessToken;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Services\Validations\Role\RoleValidationInterface;
use Illuminate\Http\Request;

class RolesController extends Controller
{

    function __construct(
        private RoleRepositoryInterface $roleRepositoryInterface,
        private RoleValidationInterface $roleValidationInterface
    ) {

        sanctum_auth();
    }

    public function index()
    {

        return $this->roleRepositoryInterface->index();
    }

    public function store(Request $request)
    {
        $data = $this->roleValidationInterface->store($request);

        return $this->roleRepositoryInterface->store($request, $data);
    }

    function getUserRolesAndDirectPermissions()
    {
        return $this->roleRepositoryInterface->getUserRolesAndDirectPermissions();
    }

    public function destroy($permissiongroup_id)
    {
        return $this->roleRepositoryInterface->destroy($permissiongroup_id);
    }
}
