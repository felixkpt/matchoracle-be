<?php

namespace  App\Repositories\CoachContract;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CoachContractRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);
}
