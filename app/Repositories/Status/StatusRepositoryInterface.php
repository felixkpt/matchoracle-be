<?php

namespace  App\Repositories\Status;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface StatusRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);
}
