<?php

namespace App\Repositories\Country;

use App\Models\Country;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CountryRepository implements CountryRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Country $model)
    {
    }

    public function index($filter = false, $ids = null)
    {

        $countries = $this->model::with(['continent', 'competitions'])
            ->when($filter, fn ($q) => $q->whereIn('id', $ids));

            Log::critical("message", [Storage::disk('local')->get('')]);

        $uri = '/admin/countries/';
        $res = SearchRepo::of($countries, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('flag', fn($q) => $q->flag ?? asset('storage/football/defaultflag.png'))
            ->addColumn('has_competitions', fn ($q) =>  $q->has_competitions ? 'Yes' : 'No')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->addFillable('has_competitions', 'has_competitions', ['input' => 'select'])
            ->orderBy('name')
            ->paginate($filter ? $this->model->count() : null);

        return response(['results' => $res]);
    }

    function whereHasClubTeams()
    {
        $ids = $this->model::where('has_competitions', true)->get()->pluck('id');

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
        $competition = $this->model::where('id', $id);

        $uri = '/admin/countries/';
        $statuses = SearchRepo::of($competition, ['id', 'name', 'slug'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('has_competitions', fn ($q) =>  $q->has_competitions ? 'Yes' : 'No')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->addFillable('has_competitions', 'has_competitions', ['input' => 'select'])
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

        // Apply search and sorting using SearchRepo
        $searchRepo = SearchRepo::of($queryBuilder, $searchableColumns, $sortableColumns);

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
