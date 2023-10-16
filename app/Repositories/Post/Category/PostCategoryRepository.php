<?php

namespace App\Repositories\Post\Category;

use App\Models\PostCategory;
use App\Models\PostStatus;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostCategoryRepository implements PostCategoryRepositoryInterface
{
    use CommonRepoActions;

    private $checked_permissions = [];

    function __construct(protected PostCategory $model)
    {
    }

    public function index()
    {

        $postcats = $this->model::query()
            ->when(isset(request()->id) && request()->id > 0, fn ($q) => $q->where('id', request()->id))
            ->when(isset(request()->n_id) && request()->n_id > 0, fn ($q) => $q->where('id', '!=', request()->n_id))
            ->when(isset(request()->parent_category_id), fn ($q) => $q->where('parent_category_id', request()->parent_category_id));
        $res = SearchRepo::of($postcats, ['id', 'name', 'image'])
            ->addColumn('name', function ($item) {

                $parent_category_id = $item->parent_category_id;

                $names = [$item->name];
                while ($parent_category_id) {
                    $cat = $this->model::find($parent_category_id);
                    $names[] = $cat->name;
                    $parent_category_id = $cat->parent_category_id;
                }

                return implode(' > ', array_reverse($names));
            })
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('action', function ($item) {
                return '
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon icon-list2 font-20"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item autotable-navigate" href="/admin/posts/' . $item->slug . '">View</a></li>
                            '
                    .
                    (checkPermission('postcats.categories', 'post') ?
                        '<li><a class="dropdown-item autotable-edit" data-id="' . $item->id . '" href="/admin/posts/categories/' . $item->id . '">Edit</a></li>'
                        :
                        '')
                    .
                    '
                            <li><a class="dropdown-item autotable-status-update" data-id="' . $item->id . '" href="/admin/posts/categories/' . $item->id . '/status-update">Status update</a></li>
                        </ul>
                    </div>
                    ';
            })
            ->paginate();

        return response(['results' => $res]);
    }


    public function store(Request $request, $validatedData)
    {

        if ($request->slug) {
            $slug = Str::slug($validatedData['slug']);
        } else {
            // Generate the slug from the name and parent_category slug
            $slug = Str::slug($validatedData['name']);


            if ($request->id) {
                $exists = $this->model::find($request->id);
                if ($exists) {
                    $request->merge(['parent_category_id' => $exists->parent_category_id]);
                }
            }

            // Check if the generated slug is unique, if not, add a prefix
            $parent_category_id = $request->parent_category_id;
            while ($parent_category_id > 0) {
                $category = $this->model::find($parent_category_id);
                if ($category) {
                    $slug = $category->slug . '-' . $slug;
                    break;
                } else {
                    break;
                }
            }

            // Check if the generated slug is unique, if not, add a suffix
            $count = 1;
            while ($this->model::where('slug', $slug)->exists()) {
                $slug = Str::slug($slug) . '-' . Str::random($count);
                $count++;
            }
        }

        // Include the generated slug in the validated data
        $validatedData['slug'] = Str::lower($slug);
 
        // Create a new PostCat instance with the validated data
        $validatedData['name'] = Str::title($validatedData['name']);

        if (key_exists('parent_category_id', $validatedData) && !$validatedData['parent_category_id'])
            $validatedData['parent_category_id'] = 0;

        $documentation = $this->autoSave($validatedData);

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
        return response(['type' => 'success', 'message' => 'Documentation category ' . $action . ' successfully', 'results' => $documentation]);
    }

    public function show($slug)
    {
        $postcats = $this->model::with('category')->where('slug', $slug);

        $res = SearchRepo::of($postcats, [], [])
            ->addColumn('name', fn ($item) => $item->name)
            ->statuses(PostStatus::select('id', 'name')->get())->first();

        return response(['results' => $res]);
    }

    public function listCatTopics($slug)
    {
        request()->merge(['slug' => $slug]);
        return app(TopicsController::class)->index();
    }
}
