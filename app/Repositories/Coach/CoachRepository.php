<?php

namespace App\Repositories\Coach;

use App\Models\Coach;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CoachRepository implements CoachRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Coach $model)
    {
    }

    public function index()
    {

        $coaches = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->with(['nationality']);
        if ($this->applyFiltersOnly) return $coaches;

        $uri = '/dashboard/teams/coaches';
        $statuses = SearchRepo::of($coaches, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addFillable('date_of_birth', ['input' => 'input', 'type' => 'date'], 'date_of_birth')
            ->orderby('name')
            ->paginate(request()->competition_id ? $coaches->count() : 20);

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
        // $countries = $this->model::with(['continent', 'country', 'gameSources'])->where('id', $id);
        $team = $this->model::with(['country', 'gameSources'])->where('id', $id);

        $uri = '/dashboard/teams/';
        $statuses = SearchRepo::of($team, ['id', 'name', 'country.name', 'slug'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->orderby('priority_number')
            ->first();

        return response(['results' => $statuses]);
    }
}
