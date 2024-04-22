<?php

namespace App\Repositories\GamePrediction;

use App\Jobs\Automation\AutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionScoreTargetOutcome;
use App\Models\Game;
use App\Models\GamePrediction;
use App\Models\GamePredictionLog;
use App\Models\GamePredictionType;
use App\Models\PredictionJobLog;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GamePredictionRepository implements GamePredictionRepositoryInterface
{

    use CommonRepoActions, AutomationTrait;

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

        if (count($predictions) > 0) {
            $competition = Competition::find($competition_id);
            // update the preds counts
            $predictionCount = GamePrediction::where('competition_id', $competition->id)
                ->where('prediction_type_id', current_prediction_type())
                ->count();
            $competition->update([
                'predictions_counts' => $predictionCount,
            ]);
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

    function predictionsJobLogs()
    {
        if (request()->increment_job_run_counts) {
            $this->loggerModel();
        } else if (request()->is_competition_run_logging) {
            $this->doCompetitionRunLogging();
        } else {
            $this->doLogging(request()->all());
        }

        return response(['results' => 'Succcess.']);
    }

    private function doLogging($data = null)
    {
        Log::alert('dDDD', $data);
        $predicted_counts = $data['results']['saved_updated'] ?? 0;
        $predition_success_counts = $predicted_counts > 0 ? 1 : 0;
        $predition_failed_counts = $data ? ($predicted_counts === 0 ? 1 : 0) : 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'prediction_run_counts' => $exists->fetch_run_counts + 1,
                'prediction_success_counts' => $exists->fetch_success_counts + $predition_success_counts,
                'prediction_failed_counts' => $exists->fetch_failed_counts + $predition_failed_counts,
                'predicted_counts' => $exists->predicted_counts + $predicted_counts,
            ];

            $exists->update($arr);

            // if ($fetch_failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedStandingLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
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
                'prediction_run_counts' => 0,
                'prediction_success_counts' => 0,
                'prediction_failed_counts' => 0,
                'predicted_counts' => 0,
            ];

            $record = PredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }

    function updateCompetitionLastTraining()
    {
        Competition::find(request()->competition_id)->lastAction()->update(['predictions_trained_to' => Carbon::parse(request()->trained_to)->format('Y-m-d'), 'predictions_last_train' => now()]);
        return response(['message' => 'Successfully updated last train time.']);
    }
}
