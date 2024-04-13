<?php

namespace App\Repositories\Coach;

use App\Models\Coach;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
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

        $uri = '/admin/teams/coaches';
        $statuses = SearchRepo::of($coaches, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->addFillable('date_of_birth', 'date_of_birth', ['input' => 'input', 'type' => 'date'])
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

        $uri = '/admin/teams/';
        $statuses = SearchRepo::of($team, ['id', 'name', 'country.name', 'slug'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addActionItem(
                [
                    'title' => 'Add Sources',
                    'action' => ['title' => 'add-sources', 'modal' => 'add-sources', 'native' => null, 'use' => 'modal']
                ],
                'Status update'
            )
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->orderby('priority_number')
            ->first();

        return response(['results' => $statuses]);
    }
}
