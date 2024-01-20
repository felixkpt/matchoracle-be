<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BettingTipsStatisticJobLog;
use App\Models\CompetitionPredictionStatisticJobLog;
use App\Models\CompetitionStatisticJobLog;
use App\Models\Game;
use App\Models\MatchesJobLog;
use App\Models\MatchJobLog;
use App\Models\PredictionJobLog;
use App\Models\SeasonJobLog;
use App\Models\StandingJobLog;
use App\Models\User;
use Illuminate\Support\Carbon;

class AutomationReportController extends Controller
{

    public function index()
    {
        $activeStatusId = activeStatusId();

        $users = $this->getUserStats($activeStatusId);
        $subscribedUsers = $this->getUserStats($activeStatusId, true);
        $tipsters = $this->getTipsterStats($activeStatusId);
        $today = now()->format('Y-m-d');

        $seasonsJobLogs = $this->getJobLogsStats(SeasonJobLog::class, $today, 'updated_seasons');

        $standingsJobLogs = [
            'historical_results' => $this->getJobLogsStats(StandingJobLog::class, $today, 'updated_standings'),
            'recent_results' => $this->getJobLogsStats(StandingJobLog::class, $today, 'updated_standings'),
        ];

        $matchesJobLogs = [
            'historical_results' => $this->getMatchesJobLogsStats(MatchesJobLog::class, 'historical_results', $today),
            'recent_results' => $this->getMatchesJobLogsStats(MatchesJobLog::class, 'recent_results', $today),
            'shallow_fixtures' => $this->getMatchesJobLogsStats(MatchesJobLog::class, 'shallow_fixtures', $today),
            'fixtures' => $this->getMatchesJobLogsStats(MatchesJobLog::class, 'fixtures', $today),
        ];

        $matchJobLogs = [
            'historical_results' => $this->getMatchJobLogsStats(MatchJobLog::class, 'historical_results', $today),
            'recent_results' => $this->getMatchJobLogsStats(MatchJobLog::class, 'recent_results', $today),
            'shallow_fixtures' => $this->getMatchJobLogsStats(MatchJobLog::class, 'shallow_fixtures', $today),
            'fixtures' => $this->getMatchJobLogsStats(MatchJobLog::class, 'fixtures', $today),
        ];

        $competitionStatisticsLogs = $this->getCompetitionStatisticsStats($today);
        $competitionPredictionStatisticsLogs = $this->getCompetitionPredictionStats($today);
        $bettingTipsStatisticLogs = $this->getBettingTipsStatisticLogsStats($today);
        
        $predictionsJobLogs = $this->getPredictionJobLogsStats($today);

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
            'betting_tips_statistics_logs' => $bettingTipsStatisticLogs,
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
            'with_full_time_results_only' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('results_status', 1)->count(),
            'with_half_and_time_results' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('results_status', 2)->count(),
            'without_results' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->where('results_status', 0)->count(),
        ];
    }

    private function getAdvancedMatchesStats()
    {
        $matches = $this->prepareGetAdvancedMatchesStats();
        $todayMatches = $this->prepareGetAdvancedMatchesStats(Carbon::today());

        return [
            'all' => $matches,
            'today' => $todayMatches,
        ];
    }

    private function getPredictionsStats($modelClass, $activeStatusId)
    {
        return [
            'totals' => $modelClass::whereHas('prediction')->count(),
            'past' => $modelClass::whereHas('prediction')->where('utc_date', '<=', now())->where('status_id', $activeStatusId)->count(),
            'upcoming' => $modelClass::whereHas('prediction')->where('utc_date', '>', now())->where('status_id', $activeStatusId)->count(),
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

    private function getJobLogsStats($modelClass, $date, $updated)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_counts, SUM(competition_run_counts) as total_competition_run_counts, SUM(fetch_run_counts) as total_fetch_run_counts, SUM(fetch_success_counts) as total_fetch_success_counts, SUM(fetch_failed_counts) as total_fetch_failed_counts, SUM(' . $updated . '_counts) as total_updated_items_counts';

        return [
            'all' => $modelClass::selectRaw($selects)->first(),
            'today' => $modelClass::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }

    private function getMatchJobLogsStats($model, $task, $date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_counts, SUM(competition_run_counts) as total_competition_run_counts, SUM(fetch_run_counts) as total_fetch_run_counts, SUM(fetch_success_counts) as total_fetch_success_counts, SUM(fetch_failed_counts) as total_fetch_failed_counts, SUM(updated_matches_counts) as total_updated_items_counts';

        return [
            'all' => $model::where('task', $task)->selectRaw($selects)->first(),
            'today' => $model::where('task', $task)->whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }

    private function getMatchesJobLogsStats($model, $task, $date)
    {
        return $this->getMatchJobLogsStats($model, $task, $date);
    }

    private function getCompetitionStatisticsStats($date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_count, SUM(competition_run_counts) as total_competition_run_counts, SUM(seasons_run_counts) as total_seasons_run_counts, SUM(games_run_counts) as total_games_run_counts';

        return [
            'all' => CompetitionStatisticJobLog::selectRaw($selects)->first(),
            'today' => CompetitionStatisticJobLog::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }

    private function getCompetitionPredictionStats($date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_count, SUM(competition_run_counts) as total_competition_run_counts, SUM(seasons_run_counts) as total_seasons_run_counts, SUM(games_run_counts) as total_games_run_counts';

        return [
            'all' => CompetitionPredictionStatisticJobLog::selectRaw($selects)->first(),
            'today' => CompetitionPredictionStatisticJobLog::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }

    private function getBettingTipsStatisticLogsStats($date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_count, SUM(types_run_counts) as total_types_run_counts, SUM(games_run_counts) as total_games_run_counts';

        return [
            'all' => BettingTipsStatisticJobLog::selectRaw($selects)->first(),
            'today' => BettingTipsStatisticJobLog::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }

    private function getPredictionJobLogsStats($date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_counts, SUM(competition_run_counts) as total_competition_run_counts, SUM(prediction_run_counts) as total_fetch_run_counts, SUM(prediction_success_counts) as total_fetch_success_counts, SUM(prediction_failed_counts) as total_fetch_failed_counts, SUM(predicted_counts) as total_updated_items_counts';

        return [
            'all' => PredictionJobLog::selectRaw($selects)->first(),
            'today' => PredictionJobLog::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }
}
