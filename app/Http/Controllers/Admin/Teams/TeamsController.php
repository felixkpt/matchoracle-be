<?php

namespace App\Http\Controllers\Admin\Teams;

use App\Http\Controllers\Controller;
use App\Repositories\Team\TeamRepositoryInterface;
use App\Services\Validations\Team\TeamValidationInterface;
use Illuminate\Http\Request;

class TeamsController extends Controller
{

    function __construct(
        private TeamRepositoryInterface $teamRepositoryInterface,
        private TeamValidationInterface $teamValidationInterface,
    ) {
    }

    function index($competition_id = null)
    {
        request()->merge(['competition_id' => $competition_id]);
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
