<?php

namespace  App\Repositories\Season;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface SeasonRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
}
