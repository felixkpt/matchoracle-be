<?php

namespace App\Repositories\GamePrediction;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Jobs\Automation\Traits\PredictionAutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionPredictionLog;
use App\Models\GamePrediction;
use App\Models\GamePredictionType;
use App\Repositories\CommonRepoActions;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamePredictionRepository implements GamePredictionRepositoryInterface
{

    use CommonRepoActions, AutomationTrait, PredictionAutomationTrait;

    protected  $model;
    protected $sourceContext;

    function __construct(GamePrediction $model)
    {
        $this->model = $model;
    }

    function raw() {}

    function storePredictions()
    {
        $data = request()->all();

        $version = $data['version'];
        $prediction_type = $data['prediction_type'];

        try {

            DB::beginTransaction();

            $prediction_type = GamePredictionType::updateOrCreate([
                'name' => $prediction_type,
            ]);

            $competition_id = $data['competition_id'];
            $carbon_date = Carbon::parse($data['date']);
            $date = $carbon_date->format('Y-m-d');

            if ($carbon_date->subDays(16) > Carbon::today())
                return response(['message' => "Saving preds skipped, the date {$date} is way far in the future!"]);

            $predictions = $data['predictions'];

            $commonModelQuery = [
                ['version', $version],
                ['prediction_type_id', $prediction_type->id],
                ['competition_id', $competition_id],
                ['date', $date],
            ];

            $this->model->where($commonModelQuery)->delete();

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
                        'prediction_type_id' => $prediction_type->id,
                        'competition_id' => $competition_id,
                        'date' => $date,
                        ...$game_pred
                    ]
                );
            }

            $record = CompetitionPredictionLog::query()->where(
                [
                    ['version', $version],
                    ['prediction_type_id', $prediction_type->id],
                    ['competition_id', $competition_id],
                    ['date', $date],
                ]
            )->first();

            $predicted_games = $this->model->where($commonModelQuery)->count();

            $predicted_games = $predicted_games > $record->predictable_games ? intval($record->predictable_games) : $predicted_games;

            $unpredicted_games = $record->predictable_games - $predicted_games;
            $unpredicted_games = $unpredicted_games < 0 ? 0 : $unpredicted_games;
            Log::info('predicted_games::-->', [$predicted_games]);
            Log::info('unpredicted_games::-->', [$unpredicted_games]);

            CompetitionPredictionLog::updateOrCreate(
                [
                    'version' => $version,
                    'prediction_type_id' => $prediction_type->id,
                    'competition_id' => $competition_id,
                    'date' => $date,
                ],
                [
                    'version' => $version,
                    'prediction_type_id' => $prediction_type->id,
                    'competition_id' => $competition_id,
                    'date' => $date,

                    'predicted_games' => $predicted_games,
                    'unpredicted_games' => $unpredicted_games
                ]
            );

            DB::commit();
        } catch (Exception $e) {
            Log::critical('GamePrediction Saving Error: ' . $e->getMessage());
            DB::rollBack();
        }

        if (count($predictions) > 0) {
            $competition = Competition::find($competition_id);
            // update the preds counts
            $predictionCount = GamePrediction::where('competition_id', $competition->id)
                ->where('prediction_type_id', current_prediction_type_id())
                ->count();
            $competition->update([
                'predictions_counts' => $predictionCount,
            ]);
        }

        return response(['message' => "Games Predictions saved successfully."]);
    }

    private function doLogging($data = null)
    {

        $created_counts = $data['results']['created_counts'] ?? 0;
        $updated_counts = $data['results']['updated_counts'] ?? 0;
        $failed_counts = $data['results']['failed_counts'] ?? 0;

        $exists = $this->predictionsLoggerModel();

        if ($exists) {
            $run_action_counts = $exists->run_action_counts + 1;
            $newAverageSeconds = (($exists->average_seconds_per_action * $exists->run_action_counts) + $data['seconds_taken']) / $run_action_counts;

            $arr = [
                'run_action_counts' => $run_action_counts,
                'average_seconds_per_action' => $newAverageSeconds,
                'created_counts' => $exists->created_counts + $created_counts,
                'updated_counts' => $exists->updated_counts + $updated_counts,
                'failed_counts' => $exists->failed_counts + $failed_counts,
            ];

            $exists->update($arr);

            // if ($fetch_failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedStandingLog(), $data);
        }
    }

    function updateCompetitionLastPrediction()
    {
        $competition = Competition::findOrFail(request()->competition_id);

        $competition->lastAction()->updateOrCreate(
            [],
            [
                'predictions_last_done' => now(),
            ]
        );

        $this->doLogging(request()->all());

        return response(['message' => 'Successfully updated or created last prediction time.']);
    }
}
