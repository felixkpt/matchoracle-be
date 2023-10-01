<?php

namespace App\Http\Controllers\Admin\Settings\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\Validations\User\UserValidationInterface;

class UsersController extends Controller
{
    function __construct(
        private UserRepositoryInterface $userRepositoryInterface,
        private UserValidationInterface $userValidationInterface,
    ) {
    }

    public function index()
    {
        return $this->userRepositoryInterface->index();
    }

    public function store(Request $request)
    {
        $data = $this->userValidationInterface->store($request);

        return $this->userRepositoryInterface->store($request, $data);
    }

    public function edit($id)
    {
        return $this->userRepositoryInterface->edit($id);
    }
}
