<?php

namespace  App\Repositories\Country;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface CountryRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    function whereHasClubTeams();

    function whereHasNationalTeams();

    public function store(Request $request, $data);

    public function show($id);

    function listCompetitions($id);
}
