<?php

namespace  App\Repositories\BettingStrategyProCon;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface BettingStrategyProConRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);
}
