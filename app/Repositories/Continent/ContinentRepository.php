<?php

namespace App\Repositories\Continent;

use App\Models\Continent;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;

class ContinentRepository implements ContinentRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Continent $model)
    {
    }

    public function index()
    {

        $continents = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()));

        if ($this->applyFiltersOnly) return $continents;

        $uri = '/continents/';
        $res = SearchRepo::of($continents, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->paginate();

        return response(['results' => $res]);
    }

    public function store(Request $request, $data)
    {

        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
