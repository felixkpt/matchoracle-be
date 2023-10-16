<?php

namespace  App\Repositories\Continent;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface ContinentRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);

}
