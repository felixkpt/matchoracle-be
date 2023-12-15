<?php

namespace App\Http\Controllers\Admin\Statistics;

use App\Http\Controllers\Controller;
use App\Repositories\Statistics\CompetitionPredictionStatisticsRepositoryInterface;

class CompetitionsPredictionsStatisticsController extends Controller
{

    function __construct(
        private CompetitionPredictionStatisticsRepositoryInterface $competitionPredictionStatisticsRepositoryInterface
    ) {
    }

    function index($competition_id = null)
    {
        request()->merge(['competition_id' => $competition_id]);
        return $this->competitionPredictionStatisticsRepositoryInterface->index();
    }
    
    function store()
    {
        return $this->competitionPredictionStatisticsRepositoryInterface->store();
    }
}
