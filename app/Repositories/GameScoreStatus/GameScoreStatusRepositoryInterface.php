<?php

namespace  App\Repositories\GameScoreStatus;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface GameScoreStatusRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);
}
