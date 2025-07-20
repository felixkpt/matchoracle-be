<?php

namespace App\Http\Controllers\Dashboard\SourcesTest;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Season;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForebetTestController extends Controller
{
    protected GameSourceStrategy $sourceContext;

    public function __construct(protected Competition $model)
    {
        $this->sourceContext = new GameSourceStrategy();
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function index(Request $request)
    {
        $job = $request->get('job');
        return match($job) {
            'match' => $this->fetchMatch($request),
            'matches' => $this->fetchMatches($request),
            'seasons' => $this->fetchSeasons($request),
            'standings' => $this->fetchStandings($request),
            default => response()->json(['error' => 'No job selected.'], 400),
        };
    }

    public function fetchSeasons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $competition_id = $request->competition_id;
        $seasonsHandler = $this->sourceContext->seasonsHandler();
        $results = $seasonsHandler->fetchSeasons($competition_id);
        return $results;
    }

    public function fetchStandings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $competition_id = $request->competition_id;
        $season_id = $request->season_id ?? Season::where('competition_id', $competition_id)
            ->orderBy('start_date', 'desc')
            ->first()
            ->id;

        $standingsHandler = $this->sourceContext->standingsHandler();
        $results = $standingsHandler->fetchStandings($competition_id, $season_id);
        return $results;
    }

    public function fetchMatches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'competition_id' => 'required|exists:competitions,id',
            'season_id' => 'nullable|exists:seasons,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $competition_id = $request->competition_id;
        $season_id = $request->season_id ?? Season::where('competition_id', $competition_id)
            ->orderBy('start_date', 'desc')
            ->first()
            ->id;

        $is_fixtures = $request->is_fixtures == 1;
        $request->merge(['shallow_fetch' => $request->shallow_fetch ?? false]);

        $matchesHandler = $this->sourceContext->matchesHandler();
        $results = $matchesHandler->fetchMatches($competition_id, $season_id, $is_fixtures);
        return $results;
    }

    public function fetchMatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id' => 'required|exists:games,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $game_id = $request->game_id;
        $request->merge(['ignore_results' => true]);

        $matchesHandler = $this->sourceContext->matchHandler();
        $results = $matchesHandler->fetchMatch($game_id);
        return $results;
    }
}