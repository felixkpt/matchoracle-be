<?php

namespace  App\Repositories\Post\Category;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface PostCategoryRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function show($slug);

    public function listCatTopics($slug);

    function statusUpdate($id);
}
