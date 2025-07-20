<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CompetitionPredictionStatisticJobLog;
use App\Models\CompetitionStatisticJobLog;
use App\Models\Game;
use App\Models\MatchesJobLog;
use App\Models\MatchJobLog;
use App\Models\PredictionJobLog;
use App\Models\SeasonJobLog;
use App\Models\StandingJobLog;
use App\Models\TrainPredictionJobLog;
use App\Models\User;
use App\Utilities\GameUtility;
use Illuminate\Support\Carbon;

class AutomationReportController extends Controller
{

    public $gameUtility;

    function __construct()
    {
        $this->gameUtility = (new GameUtility());
    }

    public function index()
    {
        sleep(0);
        $activeStatusId = activeStatusId();

        $users = $this->getUserStats($activeStatusId);
        $subscribedUsers = $this->getUserStats($activeStatusId, true);
        $tipsters = $this->getTipsterStats($activeStatusId);


        $scriptExecutionTimeInMins = 60 * 10;

        $seasonsJobLogs = $this->getJobLogsStats(SeasonJobLog::class, 'run', 60 * 12, $scriptExecutionTimeInMins);

        $standingsJobLogs = [
            'historical_results' => $this->getJobLogsStats(StandingJobLog::class, 'historical_results', 60 * 6, $scriptExecutionTimeInMins),
            'recent_results' => $this->getJobLogsStats(StandingJobLog::class, 'recent_results', 60 * 6, $scriptExecutionTimeInMins),
        ];


        $matchesJobLogs = [
            'historical_results' => $this->getJobLogsStats(MatchesJobLog::class, 'historical_results', 60 * 2, $scriptExecutionTimeInMins),
            'recent_results' => $this->getJobLogsStats(MatchesJobLog::class, 'recent_results', 60 * 1, $scriptExecutionTimeInMins),
            'shallow_fixtures' => $this->getJobLogsStats(MatchesJobLog::class, 'shallow_fixtures', 60 * 2, $scriptExecutionTimeInMins),
            'fixtures' => $this->getJobLogsStats(MatchesJobLog::class, 'fixtures', 60 * 6, $scriptExecutionTimeInMins),
        ];

        $scriptExecutionTimeInMins = 60 * 12;
        $matchJobLogs = [
            'historical_results' => $this->getJobLogsStats(MatchJobLog::class, 'historical_results', 60 * 2, $scriptExecutionTimeInMins),
            'recent_results' => $this->getJobLogsStats(MatchJobLog::class, 'recent_results', 60 * 1, $scriptExecutionTimeInMins),
            'shallow_fixtures' => $this->getJobLogsStats(MatchJobLog::class, 'shallow_fixtures', 60 * 2, $scriptExecutionTimeInMins),
            'fixtures' => $this->getJobLogsStats(MatchJobLog::class, 'fixtures', 60 * 6, $scriptExecutionTimeInMins),
        ];

        $scriptExecutionTimeInMins = 60 * 10;
        $competitionStatisticsLogs = $this->getJobLogsStats(CompetitionStatisticJobLog::class, 'run', 60 * 6, $scriptExecutionTimeInMins);
        $competitionPredictionStatisticsLogs = $this->getJobLogsStats(CompetitionPredictionStatisticJobLog::class, 'run', 60 * 6, $scriptExecutionTimeInMins);

        $scriptExecutionTimeInMins = 60 * 20;
        $predictionsJobLogs = $this->getJobLogsStats(PredictionJobLog::class, 'run', 60 * 2, $scriptExecutionTimeInMins);
        $trainPredictionsJobLogs = $this->getJobLogsStats(TrainPredictionJobLog::class, 'run', 60 * 2, $scriptExecutionTimeInMins);


        $matches = $this->getAdvancedMatchesStats();

        $results = [
            'users' => $users,
            'subscribed_users' => $subscribedUsers,
            'tipsters' => $tipsters,
            'seasons_job_logs' => $seasonsJobLogs,
            'standings_job_logs' => $standingsJobLogs,

            'matches_job_logs' => $matchesJobLogs,
            'match_job_logs' => $matchJobLogs,

            'competition_statistics_logs' => $competitionStatisticsLogs,
            'competition_prediction_statistics_logs' => $competitionPredictionStatisticsLogs,

            'competition_statistics_logs' => $competitionStatisticsLogs,
            'competition_prediction_statistics_logs' => $competitionPredictionStatisticsLogs,
            'predictions_job_logs' => $predictionsJobLogs,
            'train_predictions_job_logs' => $trainPredictionsJobLogs,
            'advanced_matches' => $matches,
        ];

        return response(['results' => $results]);
    }

    private function getMatchStatsQuery($date = null)
    {
        $query = Game::query();

        if ($date) {
            $query->whereDate('utc_date', $date);
        }

        return $query;
    }

    private function prepareGetAdvancedMatchesStats($date = null)
    {
        return [
            'totals' => $this->getMatchStatsQuery($date)->count(),
            'past' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->count(),
            'upcoming' => $this->getMatchStatsQuery($date)->where('utc_date', '>', now())->count(),
            'with_full_time_results_only' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('game_score_status_id', gameScoresStatus('ft-results-only'))->count(),
            'with_half_and_time_results' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('game_score_status_id', gameScoresStatus('ft-and-ht-results'))->count(),
            'without_results' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('game_score_status_id', gameScoresStatus('scheduled'))->count(),
        ];
    }

    private function getAdvancedMatchesStats()
    {
        $matches = $this->prepareGetAdvancedMatchesStats();
        $customMatches = $this->prepareGetAdvancedMatchesStats(Carbon::today());

        return [
            'all' => $matches,
            'custom' => $customMatches,
        ];
    }

    private function getUserStats($activeStatusId, $subscribed = false)
    {
        $query = User::query();

        if ($subscribed) {
            // $query->where('subscribed', true);
        }

        return [
            'totals' => $query->count(),
            'active' => $query->where('status_id', $activeStatusId)->count(),
            'inactive' => $query->where('status_id', '!=', $activeStatusId)->count(),
        ];
    }

    private function getTipsterStats($activeStatusId)
    {
        $userStats = $this->getUserStats($activeStatusId);

        return array_merge(
            $userStats,
            [
                'totals' => User::whereHas('votes')->count(),
                'active' => User::whereHas('votes')->where('status_id', $activeStatusId)->count(),
                'inactive' => User::whereHas('votes')->where('status_id', '!=', $activeStatusId)->count(),
            ]
        );
    }

    private function getJobLogsStats($modelClass, $task, $jobIntervalInMins, $scriptExecutionTimeInMins, $fromDate = null, $toDate = null)
    {
        $fromDate = request()->input('from_date');
        $toDate = request()->input('to_date');
        $today = now()->format('Y-m-d');

        $selects = $this->getSelects();

        // Get total stats
        $allStats = $modelClass::when($task !== 'run', fn($q) => $q->where('task', $task))->selectRaw($selects)->first();
        $allStats->remaining_time = $this->gameUtility->calculateRemainingTime(
            $modelClass,
            $jobIntervalInMins,
            $scriptExecutionTimeInMins,
            $allStats->total_action_counts,
            $allStats->total_run_action_counts,
            $allStats->total_average_seconds_per_action
        );

        $customStats = $modelClass::when($task !== 'run', fn($q) => $q->where('task', $task))
            ->when($fromDate, fn($q) => $q->whereDate('date', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('date', '<=', $toDate))
            ->when(!$fromDate && !$toDate, fn($q) => $q->whereDate('date', $today))
            ->selectRaw($selects)
            ->first();

        $customStats->remaining_time = $this->gameUtility->calculateRemainingTime(
            $modelClass,
            $jobIntervalInMins,
            $scriptExecutionTimeInMins,
            $customStats->total_action_counts,
            $customStats->total_run_action_counts,
            $customStats->total_average_seconds_per_action
        );

        return [
            'all' => $allStats,
            'custom' => $customStats,
        ];
    }

    // Reusable method to generate the base job logs query
    private function getSelects()
    {
        return 'SUM(job_run_counts) as total_job_run_counts, 
            SUM(competition_counts) as total_competition_counts, 
            SUM(run_competition_counts) as total_run_competition_counts, 
            SUM(action_counts) as total_action_counts, 
            SUM(run_action_counts) as total_run_action_counts, 
            ROUND(AVG(average_seconds_per_action)) AS total_average_seconds_per_action, 
            SUM(created_counts) as total_created_counts, SUM(updated_counts) as total_updated_counts, 
            SUM(failed_counts) as total_failed_counts';
    }
}
