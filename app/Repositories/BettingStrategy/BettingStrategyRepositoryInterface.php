<?php

namespace  App\Repositories\BettingStrategy;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface BettingStrategyRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);
}
