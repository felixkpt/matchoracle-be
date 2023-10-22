<?php

namespace App\Http\Controllers\Admin\Odds;

use App\Http\Controllers\Controller;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OddsController extends Controller
{
    private $oddsRepository;


    public function __construct()
    {

        $this->oddsRepository = autoModel(request()->year . '_odds');
    }

    public function index()
    {
        return response('Games/Index', $this->oddsRepository->all());
    }

    public function year()
    {
        $builder = $this->oddsRepository->query()->when(request()->game_id, fn ($q) => $q->where('game_id', request()->game_id));
        $odds = SearchRepo::of($builder, ['id', 'home_team', 'away_team', 'date_time', 'date'])->addColumn('1x2_odds', fn($q) => $q->home_win_odds.' '. $q->draw_odds.' '.$q->away_win_odds)->paginate(10);

        if (!request()->inertia()) return response(['results' => $odds]);

        return response('Games/Game/Index', ['odds' => $odds]);
    }

    //Create odds
    public function create()
    {
        return response('Games/Create');
    }

    //Get odds by id
    public function find(Request $request)
    {
        $id = $request->id;
        return response()->json($this->oddsRepository->find($id, ['*'], ['keytoken', 'endpoint']), 200);
    }


    function show()
    {
    }


    function list()
    {

        // Example usage
        $searchableColumns = ['title', 'content']; // Columns to search against
        $sortableColumns = ['id', 'title']; // Columns available for sorting

        // Create a query builder for the "Game" model
        $queryBuilder = Game::where([]);

        // Apply search and sorting using SearchRepo
        $searchRepo = SearchRepo::of($queryBuilder, $searchableColumns, $sortableColumns);

        // Add a custom column "image_url" to the search results
        $searchRepo->addColumn('image_url', function ($odds) {

            // Logic to generate the image URL based on the "image" field of the odds
            return asset('images/' . $odds->image);
        });

        // Paginate the search results
        $results = $searchRepo->paginate(10); // 10 items per page

        return response(['results' => $results]);
    }

    //Store odds
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|unique:oddss,title,' . $request->id . ',id',
            'content_short' => 'required',
            'content' => 'required',
            'priority_no' => 'numeric'
        ]);

        $data = $request->all();
        $this->oddsRepository->updateOrCreate(['id' => $request->id], $data);

        return to_route('oddss.index');
    }

    function update(Request $request)
    {
        return $this->store($request, true);
    }

    function destroy($id)
    {
        $this->oddsRepository->deleteById($id);
        return to_route('oddss.index');
    }
}
