<?php

namespace  App\Repositories\Coach;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CoachRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);
}
