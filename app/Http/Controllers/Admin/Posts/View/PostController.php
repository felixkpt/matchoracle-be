<?php

namespace App\Http\Controllers\Admin\Posts\View;

use App\Http\Controllers\Admin\Posts\PostsController;
use App\Http\Controllers\Controller;
use App\Repositories\Post\PostRepositoryInterface;
use Illuminate\Http\Request;

class PostController extends Controller
{
    function __construct(
        private PostRepositoryInterface $postRepositoryInterface,
    ) {
    }
    

    public function show($id)
    {
        return $this->postRepositoryInterface->show($id);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(PostsController::class)->store($request);
    }

    function updateStatus($id)
    {
        return $this->postRepositoryInterface->updateStatus($id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->postRepositoryInterface->destroy($id);
    }
}
