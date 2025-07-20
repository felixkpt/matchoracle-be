<?php

namespace App\Http\Controllers\Dashboard\Predictions;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GamePrediction\GamePredictionRepositoryInterface;
use App\Repositories\GamePrediction\TrainGamePredictionRepositoryInterface;
use Illuminate\Support\Carbon;

class PredictionsController extends Controller
{
    // Use traits for shared common controller methods
    use CommonControllerMethods;

    // Property to store the prediction mode ID
    protected $predictionModeId;

    // Constructor to initialize dependencies and set up request parameters
    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
        private GamePredictionRepositoryInterface $gamePredictionRepositoryInterface,
        private TrainGamePredictionRepositoryInterface $trainGamePredictionRepositoryInterface,
    ) {
        // Initialize repository
        $this->repo = $gameRepositoryInterface;

        // Set the prediction mode ID from the request
        $this->predictionModeId = request()->prediction_mode_id;

        // Default request parameters for predictions
        $arr = [
            'include_preds' => true,
        ];

        // Modify the order direction if type is 'upcoming'
        if (request()->type == 'upcoming') {
            $arr['order_direction'] = 'desc';
        }

        // Merge the modified parameters into the request
        request()->merge($arr);
    }

    // Retrieve all game predictions
    function index()
    {
        return $this->gameRepositoryInterface->index();
    }

    // Retrieve today's predictions
    function today()
    {
        return $this->gameRepositoryInterface->today();
    }

    // Retrieve yesterday's predictions
    function yesterday()
    {
        return $this->gameRepositoryInterface->yesterday();
    }

    // Retrieve tomorrow's predictions
    function tomorrow()
    {
        return $this->gameRepositoryInterface->tomorrow();
    }

    // Retrieve predictions for a specific year
    function year($year)
    {
        return $this->gameRepositoryInterface->year($year);
    }

    // Retrieve predictions for a specific year and month
    function yearMonth($year, $month)
    {
        return $this->gameRepositoryInterface->yearMonth($year, $month);
    }

    // Retrieve predictions for a specific year, month, and day
    function yearMonthDay($year, $month, $date)
    {
        return $this->gameRepositoryInterface->yearMonthDay($year, $month, $date);
    }

    // Retrieve predictions for a date range
    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        // Create Carbon instances for the start and end dates
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        // Retrieve predictions within the date range
        $predictions = $this->gameRepositoryInterface->dateRange($from_date, $to_date);

        return $predictions;
    }

    // Store competition prediction type statistics
    function storeCompetitionPredictionTypeStatistics()
    {
        return $this->trainGamePredictionRepositoryInterface->storeCompetitionPredictionTypeStatistics();
    }

    // Update the last training data for a competition
    function updateCompetitionLastTraining()
    {
        return $this->trainGamePredictionRepositoryInterface->updateCompetitionLastTraining();
    }

    // Store predictions for a game
    function storePredictions()
    {
        return $this->gamePredictionRepositoryInterface->storePredictions();
    }

    // Update the last prediction data for a competition
    function updateCompetitionLastPrediction()
    {
        return $this->gamePredictionRepositoryInterface->updateCompetitionLastPrediction();
    }
}
