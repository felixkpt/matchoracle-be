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
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addFillable('website', ['input' => 'input', 'type' => 'url'], 'website')
            ->addFillable('founded', ['input' => 'input', 'type' => 'number'], 'founded')
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
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->orderby('priority_number')
            ->first();

        return response(['results' => $statuses]);
    }
}
