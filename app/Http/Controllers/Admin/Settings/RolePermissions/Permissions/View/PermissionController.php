<?php

namespace App\Http\Controllers\Admin\Settings\RolePermissions\Permissions\View;

use App\Http\Controllers\Admin\Settings\RolePermissions\Permissions\PermissionsController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Services\Validations\Permission\PermissionValidationInterface;

class PermissionController extends Controller
{
    function __construct(
        private PermissionRepositoryInterface $permissionRepositoryInterface,
        private PermissionValidationInterface $permissionValidationInterface
    ) {
    }

    public function show($id)
    {
        return $this->permissionRepositoryInterface->show($id);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(PermissionsController::class)->store($request);
    }

    function updateStatus($id)
    {
        return $this->permissionRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->permissionRepositoryInterface->destroy($id);
    }
}
