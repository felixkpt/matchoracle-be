<?php

namespace App\Http\Controllers\Admin\Posts\Categories\Topics;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\PostTopic;
use App\Repositories\SearchRepo;
use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TopicsController extends Controller
{
    public function index()
    {
        $docs = PostTopic::query()
            ->when(request()->category_id, function ($q) {
                $q->where('category_id', request()->category_id);
            })
            ->when(request()->slug, function ($q) {
                $cat = PostCategory::whereslug(request()->slug)->first();
                $q->where('category_id', $cat->id);
            });

        $res = SearchRepo::of($docs, ['id', 'name', 'image'])
            ->addColumn('action', function ($item) {
                return '
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon icon-list2 font-20"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item autotable-view" data-id="' . $item->id . '" href="/admin/posts/categories/topics/view/' . $item->id . '">View</a></li>
                            '
                    .
                    (checkPermission('docs.categories.topics', 'post') ?
                        '<li><a class="dropdown-item autotable-edit" data-id="' . $item->id . '" href="/admin/posts/categories/topics/' . $item->id . '">Edit</a></li>'
                        :
                        '')
                    .
                    '
                            <li><a class="dropdown-item autotable-update-status" data-id="' . $item->id . '" href="/admin/posts/categories/' . $item->id . '/topics/view/' . $item->id . '/update-status">Status update</a></li>
                        </ul>
                    </div>
                    ';
            })
            ->paginate();

        return response(['results' => $res]);
    }

    public function create()
    {
        // Show the create docs/categories/'.$item->id.'/topics/doc page form
    }

    public function store(Request $request)
    {

        // Validate the incoming request data
        $validatedData = $request->validate([
            'category_id' => 'required|exists:post_categories,id', // Ensure id exists
            'name' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('posttopics', 'slug')->ignore($request->id),
            ],
            'description' => 'nullable|string|max:255',
            'image' => 'required|image',
            'priority_number' => 'nullable|integer|between:0,99999999',
        ]);

        if ($request->slug) {
            $slug = Str::slug($validatedData['slug']);
        } else {
            // Generate the slug from the name
            $slug = Str::slug($validatedData['name']);

            if (!$request->id) {

                // Check if the generated slug is unique, if not, add a suffix
                $count = 1;
                while (PostTopic::where('slug', $slug)->exists()) {
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

        $documentation = PostTopic::updateOrCreate(['id' => $request->id], $validatedData);

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
        return response(['type' => 'success', 'message' => 'Documentation topic ' . $action . ' successfully', 'results' => $documentation]);
    }

    public function update(Request $request, $id)
    {
        request()->merge(['id' => $id]);
        return $this->store($request);
    }
}
