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
        $posts = $this->model::query()->when(isset(request()->category_id) && request()->category_id > 0, fn ($q) => $q->where('category_id', request()->category_id));

        $res = SearchRepo::of($posts, ['title', 'content_short'], ['id', 'title', 'status', 'user_id'], ['title', 'content_short', 'content', 'image', 'status'])
            ->addColumn('action', function ($item) {
                return '
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon icon-list2 font-20"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item autotable-navigate" href="/admin/posts/view/' . $item->id . '">View</a></li>
                            '
                    .
                    (checkPermission('posts', 'post') ?
                        '<li><a class="dropdown-item autotable-navigate" data-id="' . $item->id . '" href="/admin/posts/view/' . $item->id . '/edit">Edit</a></li>'
                        :
                        '')
                    .
                    '
                            <li><a class="dropdown-item autotable-status-update" data-id="' . $item->id . '" href="/admin/posts/view/' . $item->id . '/status-update">Status update</a></li>
                        </ul>
                    </div>
                    ';
            })
            ->statuses(PostStatus::select('id', 'name')->get())
            ->paginate();

        return response(['results' => $res]);
    }

    public function create()
    {
        // Show the create posts/doc page form
    }

    public function store(Request $request, $data)
    {
        // Create a new Post instance with the validated data

        $post = $this->model::updateOrCreate(['id' => $request->id], $data);

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
        $posts = $this->model::where('id', $id);

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
    
    
}
