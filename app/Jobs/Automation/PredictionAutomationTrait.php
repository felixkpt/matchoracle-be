<?php

namespace App\Jobs\Automation;

use App\Models\GamePredictionType;
use App\Models\PredictionJobLog;
use App\Models\TrainPredictionJobLog;
use Illuminate\Support\Carbon;

trait PredictionAutomationTrait
{
    protected function trainPredictionsLoggerModel($increment_job_run_counts = false)
    {
        $prediction_type = request()->prediction_type;
        
        $prediction_type = GamePredictionType::where('name', $prediction_type)->first();

        $today = Carbon::now()->format('Y-m-d');
        $record = TrainPredictionJobLog::where('date', $today)->where('prediction_type_id', $prediction_type->id)->first();

        if (!$record) {
            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'train_success_counts' => 0,
                'train_failed_counts' => 0,
                'average_seconds_per_run' => 0,
            ];

            $record = TrainPredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }

    protected function predictionsLoggerModel($increment_job_run_counts = false)
    {
        $prediction_type = GamePredictionType::where('name', request()->prediction_type)->first();

        $today = Carbon::now()->format('Y-m-d');
        $record = PredictionJobLog::where('date', $today)->where('prediction_type_id', $prediction_type->id)->first();

        if (!$record) {
            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'prediction_success_counts' => 0,
                'prediction_failed_counts' => 0,
                'average_seconds_per_run' => 0,
            ];

            $record = PredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }
}
