<?php

namespace App\Http\Controllers\Admin\Predictions;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GamePrediction\GamePredictionRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PredictionsController extends Controller
{

    use CommonMethods;

    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
        private GamePredictionRepositoryInterface $gamePredictionRepositoryInterface,
    ) {
        request()->merge(['show_predictions' => true, 'break_preds' => true]);
    }

    function index()
    {
        return $this->gameRepositoryInterface->index();
    }

    function today()
    {
        return $this->gameRepositoryInterface->today();
    }

    function yesterday()
    {
        return $this->gameRepositoryInterface->yesterday();
    }

    function tomorrow()
    {
        return $this->gameRepositoryInterface->tomorrow();
    }

    function year($year)
    {
        return $this->gameRepositoryInterface->year($year);
    }

    function yearMonth($year, $month)
    {
        return $this->gameRepositoryInterface->yearMonth($year, $month);
    }

    function yearMonthDay($year, $month, $date)
    {
        return $this->gameRepositoryInterface->yearMonthDay($year, $month, $date);
    }

    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        $predictions = $this->gameRepositoryInterface->dateRange($from_date, $to_date);

        return $predictions;
    }

    function storePredictions()
    {
        return $this->gamePredictionRepositoryInterface->storePredictions();
    }

    function storeCompetitionScoreTargetOutcome()
    {
        return $this->gamePredictionRepositoryInterface->storeCompetitionScoreTargetOutcome();
    }

    function predictionsJobLogs()
    {
        return $this->gamePredictionRepositoryInterface->predictionsJobLogs();
    }

    function updateCompetitionLastTraining()
    {
        return $this->gamePredictionRepositoryInterface->updateCompetitionLastTraining();
    }
}
