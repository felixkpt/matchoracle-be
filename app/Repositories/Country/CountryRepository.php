<?php

namespace App\Repositories\Country;

use App\Models\Country;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CountryRepository implements CountryRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Country $model)
    {
    }

    public function index($filter = false, $ids = null)
    {

        $countries = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->where('has_competitions', true)->where('continent_id', '!=', get_world_id())->with(['continent', 'competitions'])
            ->when($filter, fn ($q) => $q->whereIn('id', $ids));

        if ($this->applyFiltersOnly) return $countries;

        $uri = '/countries/';
        $res = SearchRepo::of($countries, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('has_competitions', fn ($q) =>  $q->has_competitions ? 'Yes' : 'No')
            ->addFillable('continent_id', ['input' => 'select'], 'continent_id')
            ->addFillable('has_competitions', ['input' => 'select'], 'has_competitions')
            ->orderBy('name')
            ->paginate($filter ? $this->model->count() : null);

        return response(['results' => $res]);
    }

    function whereHasClubTeams()
    {
        $ids = $this->model::query()->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))->where('has_competitions', true)->get()->pluck('id');

        return $this->index(true, $ids);
    }

    function whereHasNationalTeams()
    {
        return $this->index(true, []);
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
        $competition = $this->model::query()->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))->where('id', $id);

        $uri = '/countries/';
        $statuses = SearchRepo::of($competition, ['id', 'name', 'slug'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('has_competitions', fn ($q) =>  $q->has_competitions ? 'Yes' : 'No')
            ->addFillable('continent_id', ['input' => 'select'], 'continent_id')
            ->addFillable('has_competitions', ['input' => 'select'], 'has_competitions')
            ->orderby('priority_number')
            ->first();

        return response(['results' => $statuses]);
    }

    function listCompetitions($id)
    {

        // Example usage
        $searchableColumns = ['name', 'competition_id']; // Columns to search against
        $sortableColumns = ['id', 'name', 'status']; // Columns available for sorting

        // Create a query builder for the "Country" model
        $queryBuilder = $this->model::with('user')->where('country_id', $id);

        $uri = '';
        // Apply search and sorting using SearchRepo
        $searchRepo = SearchRepo::of($queryBuilder, $searchableColumns, $sortableColumns)
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser');

        // Add a custom column "image_url" to the search results
        $searchRepo->addColumn('image_url', function ($country) {
            // Logic to generate the image URL based on the "image" field of the country
            return asset('images/' . $country->image);
        })->addColumn('created_by', function ($country) {
            return $country->user->name ?? '-';
        });

        // Paginate the search results
        $results = $searchRepo->paginate(10); // 10 items per page

        return response(['results' => $results]);
    }
}
