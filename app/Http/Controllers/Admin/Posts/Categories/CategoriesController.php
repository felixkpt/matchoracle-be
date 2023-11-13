<?php

namespace App\Http\Controllers\Admin\Posts\Categories;

use App\Http\Controllers\Admin\Posts\Categories\Topics\TopicsController;
use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Models\PostStatus;
use App\Repositories\Post\Category\PostCategoryRepositoryInterface;
use App\Repositories\SearchRepo;
use App\Services\Validations\Post\Category\PostCategoryValidationInterface;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    function __construct(
        private PostCategoryRepositoryInterface $postCategoryRepositoryInterface,
        private PostCategoryValidationInterface $postCategoryValidationInterface
    ) {
    }

    public function index()
    {

        return $this->postCategoryRepositoryInterface->index();

    }

    public function store(Request $request)
    {

        $validatedData = $this->postCategoryValidationInterface->store($request);

        return $this->postCategoryRepositoryInterface->store($request, $validatedData);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($slug)
    {
        $postcats = PostCategory::with('category')->where('slug', $slug);

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

    function updateStatus($id)
    {
        return $this->postCategoryRepositoryInterface->updateStatus($id);
    }
}
