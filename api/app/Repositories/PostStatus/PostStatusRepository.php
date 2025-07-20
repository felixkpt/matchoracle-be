<?php

namespace App\Repositories\PostStatus;

use App\Models\PostStatus;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostStatusRepository implements PostStatusRepositoryInterface
{
    use CommonRepoActions;

    function __construct(protected PostStatus $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()));

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        if ($this->applyFiltersOnly) return $statuses;

        $uri = '/dashboard/settings/picklists/statuses/post-statuses/';
        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->paginate();

        return response(['results' => $statuses]);
    }

    public function store(Request $request, $data)
    {

        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        return response(['type' => 'success', 'message' => 'PostStatus ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
