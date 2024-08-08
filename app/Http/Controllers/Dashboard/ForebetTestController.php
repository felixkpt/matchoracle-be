<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;

class ForebetTestController extends Controller
{

    protected GameSourceStrategy $sourceContext;

    function __construct(protected Competition $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    function index()
    {
    //    $countries = DB::connection('mysql2')->table('countries')->where('has_competitions', true)->pluck('id');
       
    //    Country::query()->whereIn('id', $countries)->update(['has_competitions' => true]);

    //    dd($countries);

        // request()->merge(['shallow_fetch' => true]);

        $competition_id = request()->test_id ?? 1340;
        // return $this->fetchSeasons($competition_id);

        $competition = Competition::find($competition_id);
        // $season = Season::where('competition_id', $competition->id)->where('is_current', false)->first();
        // $season = Season::where('competition_id', $competition->id)->whereYear('start_date', '2016')->first();

        // return $this->fetchStandings($competition->id, $season->id);
        return $this->fetchMatches($competition->id, $season->id, false);
        return $this->fetchMatch(request()->test_id ?? 1164);
    }

    function fetchSeasons($competition_id)
    {
        $seasonsHandler = $this->sourceContext->seasonsHandler();
        $seasons = $seasonsHandler->fetchSeasons($competition_id);
        dd($seasons);
    }

    function fetchStandings($competition_id, $season_id)
    {
        $standingsHandler = $this->sourceContext->standingsHandler();
        $standings = $standingsHandler->fetchStandings($competition_id, $season_id);
        dd($standings);
    }

    function fetchMatches($competition_id, $season_id, $is_fixtures)
    {
        $matchesHandler = $this->sourceContext->matchesHandler();
        $matches = $matchesHandler->fetchMatches($competition_id, $season_id, $is_fixtures);
        dd($matches);
    }

    function fetchMatch($game_id)
    {
        request()->merge(['ignore_results' => true]);
        $matchesHandler = $this->sourceContext->matchHandler();
        $match = $matchesHandler->fetchMatch($game_id);
        dd($match);
    }
}
