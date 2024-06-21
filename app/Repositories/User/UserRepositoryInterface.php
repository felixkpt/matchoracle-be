<?php

namespace App\Repositories\User;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface UserRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function create();

    public function store(Request $request, $data);

    public function show($id);

    public function edit($id);

    public function profileUpdate(Request $request);

    public function updateSelfPassword();

    public function updateOthersPassword();

    public function resendToken($userId);

    public function autoLoginUser($userId);

    public function loginLogs();
    
    public function listAttemptedLogins();
}
