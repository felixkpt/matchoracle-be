<?php

namespace App\Http\Controllers\Admin\Countries\View;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use App\Repositories\EloquentRepository;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ViewCountryController extends Controller
{
    private $countryRepository;


    public function __construct()
    {
        $this->countryRepository = new EloquentRepository(Country::class);
    }

    public function index($id)
    {

        $country = SearchRepo::of($this->countryRepository->model->where('id', $id))->first();
        return response('Countries/Country/Index', ['country' => $country]);
    }

    //Create country
    public function create()
    {
        return response('Countries/Create');
    }

    //Get country by id
    public function find(Request $request)
    {
    }

    function listCompetitions($id)
    {

        // Example usage
        $searchableColumns = ['name', 'competition_id']; // Columns to search against
        $sortableColumns = ['id', 'name', 'status']; // Columns available for sorting

        // Create a query builder for the "Country" model
        $queryBuilder = Competition::with('user')->where('country_id', $id);

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

    //Store country
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:countries,name,' . $request->id . ',id',
            'code' => 'required|unique:countries,code,' . $request->id . ',id',
            'dial_code' => 'required',
            'priority_no' => 'numeric'
        ]);

        $data = $request->all();
        $this->countryRepository->updateOrCreate(['id' => $request->id], $data);

        return to_route('countries.index');
    }

    function update(Request $request)
    {
        return $this->store($request, true);
    }

    function destroy($id)
    {
        $this->countryRepository->deleteById($id);
        return to_route('countries.index');
    }
}
