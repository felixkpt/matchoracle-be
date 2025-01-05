<?php

namespace  App\Repositories\Team;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface TeamRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    function matches($id);

    function combinedMatches($home_team_id, $away_team_id);

    function head2head($id);

    function teamLeagueDetails($id, $game_id = null);

    public function store(Request $request, $data);

    public function storeFromSource(Request $request, $data);

    public function show($id);

    function addSources(Request $request, $data);

    function updateCoach(Request $request, $data);

    public function seasons($id);

    function standings($id, $season_id = null);

    function teams($id);
}
