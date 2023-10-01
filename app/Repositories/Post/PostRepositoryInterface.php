<?php

namespace  App\Repositories\Post;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface PostRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function create();

    public function store(Request $request, $data);
    
    public function show($id);
}
