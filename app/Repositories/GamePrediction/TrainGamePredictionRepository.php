<?php

namespace App\Repositories\GamePrediction;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Jobs\Automation\Traits\PredictionAutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionPredictionTypeStatistics;
use App\Models\GamePrediction;
use App\Repositories\CommonRepoActions;
use Illuminate\Support\Carbon;

class TrainGamePredictionRepository implements TrainGamePredictionRepositoryInterface
{

    use CommonRepoActions, AutomationTrait, PredictionAutomationTrait;

    protected  $model;
    protected $sourceContext;

    function __construct(GamePrediction $model)
    {
        $this->model = $model;
    }

    function raw() {}

    function storeCompetitionPredictionTypeStatistics()
    {
        $data = request()->all();

        request()->validate([
            'prediction_type' => 'required',
            'competition_id' => 'required',
            'season_id' => 'nullable',
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
            // 'season_id' => $data['season_id'],
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

        $created_counts = $data['results']['created_counts'] ?? 0;
        $updated_counts = $data['results']['updated_counts'] ?? 0;
        $failed_counts = $data['results']['failed_counts'] ?? 0;

        $exists = $this->trainPredictionsLoggerModel();

        if ($exists) {
            $run_action_counts = $exists->run_action_counts + 1;
            $newAverageSeconds = intval((($exists->average_seconds_per_action * $exists->run_action_counts) + $data['seconds_taken']) / $run_action_counts);

            $arr = [
                'run_action_counts' => $run_action_counts,
                'average_seconds_per_action' => $newAverageSeconds,
                'created_counts' => $exists->created_counts + $created_counts,
                'updated_counts' => $exists->updated_counts + $updated_counts,
                'failed_counts' => $exists->failed_counts + $failed_counts,
            ];

            $exists->update($arr);
        }
    }

    function updateCompetitionLastTraining()
    {

        $competition = Competition::findOrFail(request()->competition_id);
        
        $season_id = request()->season_id;

        $competition->lastActions()->updateOrCreate(
            [
                'season_id' => $season_id,
            ],
            [
                'season_id' => $season_id,
                'predictions_trained_to' => Carbon::parse(request()->trained_to)->format('Y-m-d'),
                'predictions_last_train' => now(),
            ]
        );

        $this->doLogging(request()->all());

        return response(['message' => 'Successfully updated or created last train time.']);
    }
}
