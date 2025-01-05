<?php

namespace App\Http\Controllers\Dashboard\Competitions;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Competition\CompetitionRepositoryInterface;
use App\Services\Validations\Competition\CompetitionValidationInterface;
use Illuminate\Http\Request;

class CompetitionsController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private CompetitionRepositoryInterface $competitionRepositoryInterface,
        private CompetitionValidationInterface $competitionValidationInterface,
    ) {
        $this->repo = $competitionRepositoryInterface;
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

    function resultsStatistics()
    {
        return $this->competitionRepositoryInterface->resultsStatistics();
    }

    function predictionStatistics()
    {
        return $this->competitionRepositoryInterface->predictionStatistics();
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
