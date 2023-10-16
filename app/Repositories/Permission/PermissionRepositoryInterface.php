<?php

namespace  App\Repositories\Permission;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface PermissionRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);

    function getRolePermissions($id);

}
