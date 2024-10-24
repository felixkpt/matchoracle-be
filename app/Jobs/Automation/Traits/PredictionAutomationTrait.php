<?php

namespace App\Jobs\Automation\Traits;

use App\Models\Game;
use App\Models\PredictionJobLog;
use App\Models\TrainPredictionJobLog;
use App\Repositories\Game\GameRepository;
use Illuminate\Support\Carbon;

trait PredictionAutomationTrait
{
    protected function getPredictionLogRecord($logModel, $date)
    {
        $prediction_type = (new GameRepository(new Game()))->updateOrCreatePredictorOptions();

        $record = $logModel::where('date', $date)
            ->where('prediction_type_id', $prediction_type->id)
            ->first();
 
        return [
            'prediction_type' => $prediction_type,
            'record' => $record
        ];
    }

    protected function trainPredictionsLoggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        $today = Carbon::now()->format('Y-m-d');

        $result = $this->getPredictionLogRecord(TrainPredictionJobLog::class, $today);
        $prediction_type = $result['prediction_type'];
        $record = $result['record'];

        if (!$record) {
            if ($competition_counts <= 0) {
                abort(422, 'Competition counts is needed');
            }

            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = TrainPredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }

    protected function predictionsLoggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        $today = Carbon::now()->format('Y-m-d');

        $result = $this->getPredictionLogRecord(PredictionJobLog::class, $today);
        $prediction_type = $result['prediction_type'];
        $record = $result['record'];

        if (!$record) {
            if ($competition_counts <= 0) {
                abort(422, 'Competition counts is needed');
            }

            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = PredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }
}
