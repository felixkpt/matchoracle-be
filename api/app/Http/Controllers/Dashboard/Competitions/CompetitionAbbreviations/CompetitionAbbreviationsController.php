<?php

namespace App\Http\Controllers\Dashboard\Competitions\CompetitionAbbreviations;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Competition\CompetitionAbbreviation\CompetitionAbbreviationRepositoryInterface;
use App\Services\Validations\Competition\CompetitionAbbreviation\CompetitionAbbreviationValidationInterface;
use Illuminate\Http\Request;

class CompetitionAbbreviationsController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private CompetitionAbbreviationRepositoryInterface $competitionAbreviationRepositoryInterface,
        private CompetitionAbbreviationValidationInterface $competitionAbreviationValidationInterface,
    ) {
        $this->repo = $competitionAbreviationRepositoryInterface;
    }

    public function index()
    {
        return $this->competitionAbreviationRepositoryInterface->index();
    }

    public function store(Request $request)
    {
        $data = $this->competitionAbreviationValidationInterface->store($request);

        return $this->competitionAbreviationRepositoryInterface->store($request, $data);
    }

    function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return $this->store($request);
    }

    public function show($id)
    {
        return $this->competitionAbreviationRepositoryInterface->show($id);
    }
}
