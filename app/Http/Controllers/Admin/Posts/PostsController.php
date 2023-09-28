<?php

namespace App\Http\Controllers\Admin\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostStatus;
use App\Repositories\SearchRepo;
use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostsController extends Controller
{
    public function index()
    {
        $posts = Post::query()->when(isset(request()->category_id) && request()->category_id > 0, fn ($q) => $q->where('category_id', request()->category_id));

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
                            <li><a class="dropdown-item autotable-status-update" data-id="' . $item->id . '" href="/admin/posts/view/' . $item->id . '/status-update">' . ($item->status == 1 ? 'Deactivate' : 'Activate') . '</a></li>
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

    public function store(Request $request)
    {

        // Validate the incoming request data
        $validatedData = $request->validate([
            'category_id' => 'required|exists:post_categories,id',
            'topic_id' => 'nullable|exists:post_topics,id',
            'title' => 'required|string|max:255|unique:posts,title,' . $request->id . ',id', // Ensure title is unique
            'content_short' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'required|image',
            'priority_number' => 'nullable|integer|between:0,99999999',
        ]);

        if ($request->slug) {
            $slug = Str::slug($validatedData['slug']);
        } else {
            // Generate the slug from the title
            $slug = Str::slug($validatedData['title']);

            if (!$request->id) {

                // Check if the generated slug is unique, if not, add a suffix
                $count = 1;
                while (Post::where('slug', $slug)->exists()) {
                    $slug = Str::slug($slug) . '-' . Str::random($count);
                    $count++;
                }
            }
        }

        // Include the generated slug in the validated data
        $validatedData['slug'] = Str::lower($slug);
        if (!$request->id) {
            $validatedData['user_id'] = auth()->user()->id;
        }

        // Create a new Documentation instance with the validated data

        $documentation = Post::updateOrCreate(['id' => $request->id], $validatedData);

        if (request()->hasFile('image')) {
            $uploader = new FilesController();
            $image_data = $uploader->saveFiles($documentation, [request()->file('image')]);

            $path = $image_data[0]['path'] ?? null;
            $documentation->image = $path;
            $documentation->save();
        }

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Documentation page ' . $action . ' successfully', 'results' => $documentation]);
    }
}
