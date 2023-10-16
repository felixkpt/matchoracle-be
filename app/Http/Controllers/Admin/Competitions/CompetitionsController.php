<?php

namespace App\Http\Controllers\Admin\Competitions;

use App\Http\Controllers\Controller;
use App\Repositories\Competition\CompetitionRepositoryInterface;
use App\Services\Validations\Competition\CompetitionValidationInterface;
use Illuminate\Http\Request;

class CompetitionsController extends Controller
{

    function __construct(
        private CompetitionRepositoryInterface $competitionRepositoryInterface,
        private CompetitionValidationInterface $competitionValidationInterface,
    ) {
    }

    function index()
    {
        return $this->competitionRepositoryInterface->index();
    }

    function countryCompetitions($id)
    {
        request()->merge(['country_id' => $id]);
        return $this->competitionRepositoryInterface->index();
    }

    function store(Request $request)
    {

        if ($request->competition_origin == 'source') {
        
            $data = $this->competitionValidationInterface->storeFromSource();
        
            return $this->competitionRepositoryInterface->storeFromSource($request, $data);
        }

        $data = $this->competitionValidationInterface->store();

        return $this->competitionRepositoryInterface->store($request, $data);
    }

    function storeFetch(Request $request)
    {

        $this->competitionValidationInterface->storeFetch();

        return $this->competitionRepositoryInterface->storeFetch($request);
    }
}
