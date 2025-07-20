<?php

namespace  App\Repositories\PostStatus;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface PostStatusRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);
    
    public function show($id);
}
