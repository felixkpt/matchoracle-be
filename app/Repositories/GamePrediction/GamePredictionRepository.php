<?php

namespace App\Repositories\GamePrediction;

use App\Models\CompetitionScoreTargetOutcome;
use App\Models\Game;
use App\Models\GamePrediction;
use App\Models\GamePredictionLog;
use App\Models\GamePredictionType;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
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

    function raw()
    {

        $type_cb = function ($q) {
            $before = request()->before ?? Carbon::now();
            request()->type == 'played' ? $q->where('date', '<', $before) : (request()->type == 'upcoming' ? $q->where('date', '>=', Carbon::now()) :  $q);
        };

        Log::info('dsdf', [request()->competition_id,]);

        $preds = $this->model
            // ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->from_date, fn ($q) => $q->whereDate('date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn ($q) => $q->whereDate('date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->when(request()->date, fn ($q) => $q->whereDate('date', '=', Carbon::parse(request()->date)->format('Y-m-d')))
            ->when(!request()->date && request()->type, $type_cb);

        $results = SearchRepo::of($preds, ['id'])
            ->addColumn('utc_date', fn ($q) => $q->date)
            ->addColumn('cs_target', fn ($q) => $q->score ? game_scores($q->score) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? game_scores($q->score, true) : '-')
            ->paginate();

        return response(['results' => $results]);
    }

    function storePredictions()
    {
        $data = request()->all();

        $version = $data['version'];
        $type = $data['type'];

        $prediction_type = GamePredictionType::updateOrCreate([
            'name' => $type,
        ]);

        $competition_id = $data['competition_id'];
        $carbon_date = Carbon::parse($data['date']);
        $date = $carbon_date->format('Y-m-d');

        if ($carbon_date->subDays(16) > Carbon::today())
            return response(['message' => "Saving preds skipped, the date {$date} is way far in the future!"]);

        $predictions = $data['predictions'];

        foreach ($predictions as $game_pred) {

            $this->model->updateOrCreate(
                [
                    'version' => $version,
                    'prediction_type_id' => $prediction_type->id,
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
            $unpredicted_games = $unpredicted_games < 0 ? 0 : $unpredicted_games;

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
            'over15_target' => 3,
            'over25_target' => 4,
            'over35_target' => 5,
            'cs_target' => 6,
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
