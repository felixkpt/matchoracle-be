<?php

namespace App\Http\Controllers\Admin\Predictions;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GamePrediction\GamePredictionRepositoryInterface;
use Illuminate\Http\Request;

class PredictionsController extends Controller
{

    use CommonMethods;
    
    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
        private GamePredictionRepositoryInterface $gamePredictionRepositoryInterface,
    ) {
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

    function storeFromPythonApp()
    {
        return $this->gamePredictionRepositoryInterface->store();
    }
}
