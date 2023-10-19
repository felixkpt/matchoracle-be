<?php

namespace App\Http\Controllers\Admin\Settings\Users\View;

use App\Repositories\User\UserRepositoryInterface;
use App\Services\Validations\User\UserValidationInterface;

use App\Http\Controllers\Admin\Settings\Users\UsersController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function __construct(
        private UserRepositoryInterface $userRepositoryInterface,
        private UserValidationInterface $userValidationInterface,
    ) {
    }

    public function show($id)
    {
        return $this->userRepositoryInterface->show($id);
    }

    public function edit($id)
    {
        return $this->userRepositoryInterface->edit($id);
    }

    public function update(Request $request, $id)
    {
        $request->merge(['id' => strtolower($request->id)]);

        return app(UsersController::class)->store($request, $id);
    }

    function profileShow()
    {
    }

    public function profileUpdate(Request $request)
    {
        return $this->userRepositoryInterface->profileUpdate($request);
    }

    public function updateSelfPassword()
    {
        return $this->userRepositoryInterface->updateSelfPassword();
    }

    public function updateOthersPassword()
    {
        return $this->userRepositoryInterface->updateOthersPassword();
    }

    public function loginUser($userId)
    {
        return $this->userRepositoryInterface->loginUser($userId);
    }

    public function listAttemptedLogins()
    {
        return $this->userRepositoryInterface->listAttemptedLogins();
    }

    function updateStatus($id)
    {
        return $this->userRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->userRepositoryInterface->destroy($id);
    }
}
