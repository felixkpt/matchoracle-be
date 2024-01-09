<?php

namespace App\Http\Controllers\Admin\Odds\View;

use App\Http\Controllers\Controller;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OddsController extends Controller
{
    private $gameRepository;
    private $id;


    public function index()
    {
        $builder = $this->gameRepository->where('id', $this->id);
        $game = SearchRepo::of($builder)->first();
        return response('Games/Game/Index', ['game' => $game]);
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
        $searchRepo->addColumn('image_url', function ($game) {

            // Logic to generate the image URL based on the "image" field of the game
            return asset('images/' . $game->image);
        });

        // Paginate the search results
        $results = $searchRepo->paginate(10); // 10 items per page

        return response(['results' => $results]);
    }

    //Store game
    public function store(Request $request)
    {

        $request->validate([
            'title' => 'required|unique:games,title,' . $request->id . ',id',
            'content_short' => 'required',
            'content' => 'required',
            'priority_no' => 'numeric'
        ]);

        $data = $request->all();
        $this->gameRepository->updateOrCreate(['id' => $request->id], $data);

        return to_route('games.index');
    }

    function update(Request $request)
    {
        return $this->store($request, true);
    }

    function destroy($id)
    {
        $this->gameRepository->deleteById($id);
        return to_route('games.index');
    }
}
