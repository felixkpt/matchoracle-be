<?php

namespace App\Http\Controllers\Admin\Posts\View;

use App\Http\Controllers\Admin\Posts\PostsController;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostStatus;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show($id)
    {
        $posts = Post::where('id', $id);

        $res = SearchRepo::of($posts, [], [])
            ->addColumn('content', fn ($item) => refreshTemporaryTokensInString($item->content))
            ->statuses(PostStatus::select('id', 'name')->get())->first();

        return response(['results' => $res]);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(PostsController::class)->store($request);
    }

    public function destroy($id)
    {
        // Delete a posts/doc page
    }
}
