<?php

namespace  App\Repositories\Team;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface TeamRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store(Request $request, $data);

    public function storeFetch(Request $request);

    public function show($id);

    function addSources(Request $request, $data);

    public function seasons($id);

    function standings($id, $season_id = null);

    function teams($id);
}
