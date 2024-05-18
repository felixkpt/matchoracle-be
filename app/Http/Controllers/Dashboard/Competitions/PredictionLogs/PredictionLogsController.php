<?php

namespace App\Http\Controllers\Dashboard\Competitions\PredictionLogs;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Competition\PredictionLog\CompetitionPredictionLogRepositoryInterface;

class PredictionLogsController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private CompetitionPredictionLogRepositoryInterface $predLogRepo,
    ) {
        $this->repo = $predLogRepo;
    }

    public function index()
    {
        return $this->predLogRepo->index();
    }

    public function show($id)
    {
        return $this->predLogRepo->show($id);
    }
}
