<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Models\PostStatus;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Http\Request;

class PostRepository implements PostRepositoryInterface
{
    use CommonRepoActions;

    private $checked_permissions = [];

    function __construct(protected Post $model)
    {
    }

    public function index()
    {

        // Benchmark::dd([
        //     'Post 1' => fn () => Post::first(),
        //     'Post 5' => fn () => Post::first(),
        // ]);

        $posts = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->with(['user', 'status'])
            ->when(isset(request()->category_id) && request()->category_id > 0, fn ($q) => $q->where('category_id', request()->category_id));

        if ($this->applyFiltersOnly) return $posts;

        $uri = '/dashboard/posts/';
        $res = SearchRepo::of($posts, ['title', 'content_short', 'status', 'user_id'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'link' . 'link'))
            ->htmls(['Status'])
            ->statuses(PostStatus::select('id', 'name', 'icon', 'class')->get())
            ->paginate();

        return response(['results' => $res]);
    }


    public function store(Request $request, $data)
    {
        // Create a new Post instance with the validated data
        $post = $this->autoSave($data);

        if (request()->hasFile('image')) {

            $uploader = new FilesController();
            $image_data = $uploader->saveFiles($post, [request()->file('image')]);

            $path = $image_data[0]['path'] ?? null;
            $post->image = $path;
            $post->save();
        }

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Post page ' . $action . ' successfully', 'results' => $post]);
    }

    public function show($id)
    {

        $post = $this->model::with(['category', 'topic'])->where('id', $id);

        $res = SearchRepo::of($post, [], [])
            ->addColumn('content', fn ($item) => refreshTemporaryTokensInString($item->content))
            ->addColumn('image', fn ($item) => $item->image ? assetUriWithToken($item->image) : $item->image)
            ->statuses(PostStatus::select('id', 'name', 'icon', 'class')->get())->first();

        return response(['results' => $res]);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(PostsController::class)->store($request);
    }
}
