<?php

namespace App\Repositories\GamePrediction;

use App\Jobs\Automation\AutomationTrait;
use App\Jobs\Automation\PredictionAutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionPredictionTypeStatistics;
use App\Models\GamePrediction;
use App\Repositories\CommonRepoActions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TrainGamePredictionRepository implements TrainGamePredictionRepositoryInterface
{

    use CommonRepoActions, AutomationTrait, PredictionAutomationTrait;

    protected  $model;
    protected $sourceContext;

    function __construct(GamePrediction $model)
    {
        $this->model = $model;
    }

    function raw()
    {
    }

    function storeCompetitionPredictionTypeStatistics()
    {
        $data = request()->all();

        request()->validate([
            'prediction_type' => 'required',
            'competition_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
            'score_target_outcome_id' => 'required',
        ]);

        $score_target_outcome_ids = [
            'ft_hda_target' => 1,
            'ht_hda_target' => 2,
            'bts_target' => 3,
            'over15_target' => 4,
            'over25_target' => 5,
            'over35_target' => 6,
            'cs_target' => 7,
        ];

        $score_target_outcome_id = $score_target_outcome_ids[$data['score_target_outcome_id']];

        $data = [
            'prediction_type' => $data['prediction_type'],
            'competition_id' => $data['competition_id'],
            'train_counts' => $data['train_counts'],
            'test_counts' => $data['test_counts'],
            'score_target_outcome_id' => $score_target_outcome_id,
            'occurrences' => json_encode($data['occurrences']),
            'last_predicted' => json_encode($data['last_predicted']),
            'accuracy_score' => $data['accuracy_score'],
            'precision_score' => $data['precision_score'],
            'f1_score' => $data['f1_score'],
            'average_score' => $data['average_score'],
            'from_date' => Carbon::parse($data['from_date'])->format('Y-m-d'),
            'to_date' => Carbon::parse($data['to_date'])->format('Y-m-d'),
            'user_id' => auth()->id(),
            'status_id' => activeStatusId(),

        ];

        CompetitionPredictionTypeStatistics::updateOrCreate(
            [
                'prediction_type' => $data['prediction_type'],
                'competition_id' => $data['competition_id'],
                'score_target_outcome_id' => $data['score_target_outcome_id'],
            ],
            $data
        );

        return response(['message' => "Competition Score Target Outcomes saved successfully."]);
    }

    private function doLogging($data = null)
    {

        $trained_counts = $data['results']['saved_updated'] ?? 0;
        $train_success_counts = $trained_counts > 0 ? 1 : 0;
        $train_failed_counts = $data ? ($trained_counts === 0 ? 1 : 0) : 0;

        $exists = $this->trainPredictionsLoggerModel();

        if ($exists) {
            $competition_run_counts = $exists->competition_run_counts + 1;
            $newAverageMinutes = (($exists->average_seconds_per_run * $exists->competition_run_counts) + $data['minutes_taken']) / $competition_run_counts;

            $arr = [
                'competition_run_counts' => $competition_run_counts,
                'train_success_counts' => $exists->train_success_counts + $train_success_counts,
                'train_failed_counts' => $exists->train_failed_counts + $train_failed_counts,
                'average_seconds_per_run' => $newAverageMinutes,
            ];

            $exists->update($arr);
        }
    }

    function updateCompetitionLastTraining()
    {
        $competition = Competition::findOrFail(request()->competition_id);

        $competition->lastAction()->updateOrCreate(
            [],
            [
                'predictions_trained_to' => Carbon::parse(request()->trained_to)->format('Y-m-d'),
                'predictions_last_train' => now(),
            ]
        );

        $this->doLogging(request()->all());

        return response(['message' => 'Successfully updated or created last train time.']);
    }

}
