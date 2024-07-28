<?php

namespace App\Http\Controllers\Dashboard\Teams;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepositoryInterface;
use App\Services\Validations\Team\TeamValidationInterface;
use Illuminate\Http\Request;

class TeamsController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private TeamRepositoryInterface $teamRepositoryInterface,
        private TeamValidationInterface $teamValidationInterface,
    ) {
        $this->repo = $teamRepositoryInterface;
    }

    function index()
    {
        return $this->teamRepositoryInterface->index();
    }

    function store(Request $request)
    {

        if ($request->team_origin == 'source') {

            $data = $this->teamValidationInterface->storeFromSource();

            return $this->teamRepositoryInterface->storeFromSource($request, $data);
        }

        $data = $this->teamValidationInterface->store();

        return $this->teamRepositoryInterface->store($request, $data);
    }
}
