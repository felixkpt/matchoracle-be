<?php

namespace  App\Repositories\Competition;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CompetitionRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function storeFromSource(Request $request, $data);

    function standings($id, $season_id = null);

    function fetchSeasons($id, $data);

    function fetchStandings($id, $data);

    function fetchMatches($id, $data);

    public function show($id);

    function addSources(Request $request, $data);

    function listSources($id);

    public function seasons($id);

    function teams($id);
    
    function odds($id);
    
    function statistics($id);
    
    function predictionStatistics($id);
    
    function doStatistics($id);
    
    function tabs($id);
}
