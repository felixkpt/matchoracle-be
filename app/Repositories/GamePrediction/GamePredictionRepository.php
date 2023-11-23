<?php

namespace App\Repositories\GamePrediction;

use App\Models\CompetitionScoreTargetOutcome;
use App\Models\Game;
use App\Models\GamePrediction;
use App\Models\GamePredictionLog;
use App\Repositories\CommonRepoActions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GamePredictionRepository implements GamePredictionRepositoryInterface
{

    use CommonRepoActions;

    protected  $model;
    protected $sourceContext;

    function __construct(GamePrediction $model)
    {
        $this->model = $model;
    }

    function storePredictions()
    {
        $data = request()->all();

        $version = $data['version'];
        $type = $data['type'];
        $competition_id = $data['competition_id'];
        $date = Carbon::parse($data['date'])->format('Y-m-d');
        $predictions = $data['predictions'];

        foreach ($predictions as $game_pred) {

            $this->model->updateOrCreate(
                [
                    'version' => $version,
                    'type' => $type,
                    'competition_id' => $competition_id,
                    'game_id' => $game_pred['id'],
                ],
                [
                    'version' => $version,
                    'type' => $type,
                    'competition_id' => $competition_id,
                    'date' => $date,
                    ...$game_pred
                ]
            );

            $predicted_games = $this->model->where('date', $date)->count();
            $games = Game::whereDate('utc_date', $date);
            $total_games = $games->count();
            $unpredicted_games = $total_games - $predicted_games;

            GamePredictionLog::updateOrCreate(
                ['date' => $date],
                [
                    'date' => $date,
                    'total_games' => $total_games,
                    'predicted_games' => $predicted_games,
                    'unpredicted_games' => $unpredicted_games
                ]
            );
        }

        return response(['message' => "Games Predictions saved successfully."]);
    }

    function storeCompetitionScoreTargetOutcome()
    {
        $data = request()->all();

        request()->validate([
            'prediction_type' => 'required',
            'competition_id' => 'required',
            'from_date' => 'required',
            'to_date' => 'required',
        ]);

        $score_target_outcome_ids = [
            'hda_target' => 1,
            'bts_target' => 2,
            'over25_target' => 3,
            'cs_target' => 4,
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

        CompetitionScoreTargetOutcome::updateOrCreate(
            [
                'prediction_type' => $data['prediction_type'],
                'competition_id' => $data['competition_id'],
                'score_target_outcome_id' => $data['score_target_outcome_id'],
            ],
            $data
        );

        Log::info('DATA', $data);
        return response(['message' => "Competition Score Target Outcomes saved successfully."]);
    }
}
