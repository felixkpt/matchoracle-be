<?php

namespace App\Repositories\CoachContract;

use App\Models\CoachContract;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class CoachContractRepository implements CoachContractRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CoachContract $model)
    {
    }

    public function index()
    {

        $contracts = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()));

        if ($this->applyFiltersOnly) return $contracts;

        $uri = '/admin/teams/contracts';
        $statuses = SearchRepo::of($contracts, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->addFillable('start', 'start', ['input' => 'input', 'type' => 'date'])
            ->addFillable('until', 'until', ['input' => 'input', 'type' => 'date'])
            ->orderby('name')
            ->paginate(request()->competition_id ? $contracts->count() : 20);

        return response(['results' => $statuses]);
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
    }
}
