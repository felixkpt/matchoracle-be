<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Referee;
use App\Models\Season;
use App\Models\Team;
use App\Services\GameSources\FootballData\Seasons;
use App\Services\GameSources\Forebet\ForebetInit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ForebetTestController extends Controller
{

    protected $api;

    function __construct()
    {
        $this->api = new ForebetInit();
    }

    function index()
    {

        $competition_id = 232;

        // return $this->fetchSeasons($competition_id);

        $competition = Competition::find($competition_id);
        $season = Season::where('competition_id', $competition->id)->where('is_current', true)->first();
        // $season = Season::where('competition_id', $competition->id)->whereYear('start_date', '2023')->first();

        // return $this->fetchStandings($competition->id, $season->id);
        return $this->fetchMatches($competition->id, $season->id, true);
    }

    function fetchSeasons($season_id)
    {
        $competitions = $this->api->seasonsHandler;
        $seasons = $competitions->fetchSeasons($season_id);
        dd($seasons);
    }

    function fetchStandings($competition_id, $season_id)
    {
        $competitionsHandler = $this->api->standingsHandler;
        $standings = $competitionsHandler->fetchStandings($competition_id, $season_id);
        dd($standings);
    }

    function fetchMatches($competition_id, $season_id, $is_fixtures)
    {
        $matchesHandler = $this->api->matchesHandler;
        $matches = $matchesHandler->fetchMatches($competition_id, $season_id, $is_fixtures);
        dd($matches);
    }
}
