<?php

namespace App\Repositories\Continent;

use App\Models\Continent;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
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

        $uri = '/admin/continents/';
        $res = SearchRepo::of($continents, ['id', 'name'])
            ->addColumn('Flag', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->flag ? asset($q->flag) : asset('storage/football/defaultflag.png')) . '" />')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addActionColumn('action', $uri)
            ->htmls(['Status', 'Flag'])
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
