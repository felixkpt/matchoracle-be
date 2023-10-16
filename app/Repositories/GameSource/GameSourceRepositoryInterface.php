<?php

namespace  App\Repositories\GameSource;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface GameSourceRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($id);

}
