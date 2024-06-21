<?php

namespace App\Repositories\Venue;

use App\Models\Venue;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VenueRepository implements VenueRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Venue $model)
    {
    }

    public function index()
    {
        sleep(1);

        $teams = $this->model::query()
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id));

        $uri = '/dashboard/teams/venues';
        $statuses = SearchRepo::of($teams, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn(
                'action',
                $uri,
                [
                    'view'  => 'native',
                    'edit'  => 'modal',
                    'hide'  => null
                ]
            )
            ->addFillable('website', ['input' => 'input', 'type' => 'url'], 'website')
            ->addFillable('founded', ['input' => 'input', 'type' => 'number'], 'founded')
            ->htmls(['Status', 'Crest'])
            ->orderby('name')
            ->paginate(request()->competition_id ? $teams->count() : 20);

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
            ->addActionColumn(
                'action',
                $uri,
                [
                    'view'  => 'native',
                    'edit'  => 'modal',
                    'hide'  => null
                ]
            )
            ->htmls(['Status', 'Crest'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->orderby('priority_number')
            ->first();

        return response(['results' => $statuses]);
    }
}
