<?php

namespace App\Repositories\Competition;

use App\Jobs\Automation\CompetitionAbbreviationsJob;
use App\Jobs\Automation\MatchesHandlerJob;
use App\Jobs\Automation\MatchHandlerJob;
use App\Jobs\Automation\OddHandlerJob;
use App\Jobs\Automation\PredictionsHandlerJob;
use App\Jobs\Automation\SeasonsHandlerJob;
use App\Jobs\Automation\StandingsHandlerJob;
use App\Jobs\Automation\Statistics\CompetitionPredictionStatisticsJob;
use App\Jobs\Automation\Statistics\CompetitionStatisticsJob;
use App\Jobs\Automation\TrainPredictionsHandlerJob;
use App\Models\Competition;
use App\Repositories\CommonRepoActions;
use Illuminate\Support\Facades\Log;

class UpdateCompetitionActionRepo implements UpdateCompetitionActionRepoInterface
{
    use CommonRepoActions;

    public function __construct(protected Competition $model) {}

    public function updateAction($competitionId, $action)
    {
        // Retrieve the competition by ID
        $competition = $this->model->where('id', $competitionId);
        Log::info('competitionId:', [$competitionId]);

        if ($competition->count() == 0) {
            return response(['message' => "Competition #{$competitionId} not found."], 422);
        }

        $competition = $competition->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))->first();

        if (!$competition) {
            return response(['message' => "Competition #{$competitionId} is not active."], 422);
        }

        // Set the jobID
        $jobId = request()->job_id ?? str()->random(6);

        $ignoreTiming = true;

        // Switch based on the action to update specific fields in lastAction
        switch ($action) {
            case 'abbreviation_last_fetch':
                dispatch(new CompetitionAbbreviationsJob('fetch', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'seasons_last_fetch':
                dispatch(new SeasonsHandlerJob('fetch', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'standings_recent_results_last_fetch':
                dispatch(new StandingsHandlerJob('recent_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'standings_historical_results_last_fetch':
                dispatch(new StandingsHandlerJob('historical_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'matches_recent_results_last_fetch':
                // Dispatch the job for recent results
                dispatch(new MatchesHandlerJob('recent_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'matches_historical_results_last_fetch':
                // Dispatch the job for historical results
                dispatch(new MatchesHandlerJob('historical_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'matches_fixtures_last_fetch':
                // Dispatch the job for fixtures
                dispatch(new MatchesHandlerJob('fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'matches_shallow_fixtures_last_fetch':
                // Dispatch the job for shallow fixtures
                dispatch(new MatchesHandlerJob('shallow_fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'match_recent_results_last_fetch':
                // Dispatch job for recent results
                dispatch(new MatchHandlerJob('recent_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'match_historical_results_last_fetch':
                // Dispatch job for historical results
                dispatch(new MatchHandlerJob('historical_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'match_fixtures_last_fetch':
                // Dispatch job for fixtures
                dispatch(new MatchHandlerJob('fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'match_shallow_fixtures_last_fetch':
                // Dispatch job for shallow fixtures
                dispatch(new MatchHandlerJob('shallow_fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'odd_recent_results_last_fetch':
                // Dispatch job for recent results
                dispatch(new OddHandlerJob('recent_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'odd_historical_results_last_fetch':
                // Dispatch job for historical results
                dispatch(new OddHandlerJob('historical_results', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'odd_fixtures_last_fetch':
                // Dispatch job for fixtures
                dispatch(new OddHandlerJob('fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'odd_shallow_fixtures_last_fetch':
                // Dispatch job for shallow fixtures
                dispatch(new OddHandlerJob('shallow_fixtures', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'predictions_last_train':
                dispatch(new TrainPredictionsHandlerJob('train', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'predictions_last_done':
                dispatch(new PredictionsHandlerJob('prediction', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'stats_last_done':
                dispatch(new CompetitionStatisticsJob('stats', $jobId, $ignoreTiming, $competitionId));
                break;

            case 'predictions_stats_last_done':
                dispatch(new CompetitionPredictionStatisticsJob('stats', $jobId, $ignoreTiming, $competitionId));
                break;

            default:
                return response(['message' => 'Invalid action'], 422);
        }

        sleep(2);

        return response(['message' => ucfirst(str_replace('_', ' ', $action)) . ' updated successfully'], 200);
    }
}
