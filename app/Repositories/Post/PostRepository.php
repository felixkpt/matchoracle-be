<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Models\PostStatus;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostRepository implements PostRepositoryInterface
{
    use CommonRepoActions;

    private $checked_permissions = [];

    function __construct(protected Post $model)
    {
    }

    public function index()
    {
        $posts = $this->model::with(['user', 'status'])
            ->when(isset(request()->category_id) && request()->category_id > 0, fn ($q) => $q->where('category_id', request()->category_id));

        $res = SearchRepo::of($posts, ['title', 'content_short', 'status', 'user_id'])
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
            ->addColumn('Created_at', fn ($q) => $q->created_at->toDayDateTimeString())
            ->addColumn('Status', function ($q) {
                $status = $q->status;
                if ($status) {
                    return '<div class="d-flex justify-content-center align-items-center"><iconify-icon icon="'.$status->icon.'" class="me-1"></iconify-icon>'.Str::ucfirst(Str::replace('_', ' ', $status->name)).'</div>';
                } else return null;
            })
            ->statuses(PostStatus::select('id', 'name', 'icon', 'class')->get())
            ->paginate();

        return response(['results' => $res]);
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
        // Benchmark::dd([
        //     'Post 1' => fn () => Post::first(),
        //     'Post 5' => fn () => Post::first(),
        // ]);

        $posts = $this->model::with(['category', 'topic'])->where('id', $id);

        $res = SearchRepo::of($posts, [], [])
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
