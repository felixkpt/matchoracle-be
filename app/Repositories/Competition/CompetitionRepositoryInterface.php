<?php

namespace  App\Repositories\Competition;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CompetitionRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function storeFromSource(Request $request, $data);

    function fetchStandings($id);

    public function show($id);

    function addSources(Request $request, $data);

    function listSources($id);

    public function seasons($id);

    function standings($id, $season_id = null);

    function teams($id);
}
