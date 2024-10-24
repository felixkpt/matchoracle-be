<?php

namespace App\Http\Controllers\Dashboard\Settings\System;

use App\Http\Controllers\Controller;
use App\Models\SeasonJobLog;
use App\Models\MatchesJobLog;
use App\Models\MatchJobLog;
use App\Models\PredictionJobLog;
use App\Models\StandingJobLog;
use App\Models\TrainPredictionJobLog;
use App\Repositories\SearchRepo\SearchRepo;
use App\Utilities\GameUtility;
use Illuminate\Support\Carbon;

class LogsController extends Controller
{
    public $gameUtility;

    function __construct()
    {
        $this->gameUtility = (new GameUtility());
    }

    public function index()
    {
        $data = [];
        return response(['results' => $data]);
    }

    private function getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins)
    {
        return SearchRepo::of($model, ['date'])
            ->addColumn('Remaining_time', function ($q) use ($model, $jobIntervalInMins, $scriptExecutionTimeInMins) {
                // Retrieve necessary parameters for calculation
                $totalActions = $q->action_counts;
                $runActions = $q->run_action_counts;
                $avgSecondsPerAction = $q->average_seconds_per_action;

                // Call the calculateRemainingTime function
                return $this->gameUtility->calculateRemainingTime(
                    $model,
                    $jobIntervalInMins,
                    $scriptExecutionTimeInMins,
                    $totalActions,
                    $runActions,
                    $avgSecondsPerAction
                ) ?? 'N/A';
            })
            ->addColumn('Last_run', fn($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();
    }

    public function seasonsJobLogs()
    {
        $model = SeasonJobLog::query();
        $jobIntervalInMins = 60 * 6;
        $scriptExecutionTimeInMins = 10;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }

    public function standingsJobLogs()
    {
        $model = StandingJobLog::query()
            ->when(request()->task, fn($q) => $q->where('task', request()->task));
        $jobIntervalInMins = 60 * 4;
        $scriptExecutionTimeInMins = 10;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }

    public function matchesJobLogs()
    {
        $model = MatchesJobLog::query()
            ->when(request()->task, fn($q) => $q->where('task', request()->task));
        $jobIntervalInMins = 60 * 2;
        $scriptExecutionTimeInMins = 10;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }

    public function matchJobLogs()
    {
        $model = MatchJobLog::query()
            ->when(request()->task, fn($q) => $q->where('task', request()->task));
        $jobIntervalInMins = 60 * 3;
        $scriptExecutionTimeInMins = 10;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }

    public function trainPredictionsJobLogs()
    {
        $model = TrainPredictionJobLog::query();
        $jobIntervalInMins = 60 * 2;
        $scriptExecutionTimeInMins = 20;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }

    public function predictionsJobLogs()
    {
        $model = PredictionJobLog::query();
        $jobIntervalInMins = 60 * 2;
        $scriptExecutionTimeInMins = 20;

        $data = $this->getJobLogs($model, $jobIntervalInMins, $scriptExecutionTimeInMins);

        return response(['results' => $data]);
    }
}
