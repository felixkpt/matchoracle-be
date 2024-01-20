<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BettingTipsStatistic;
use App\Models\Competition;
use App\Models\CompetitionPredictionStatisticJobLog;
use App\Models\CompetitionStatisticJobLog;
use App\Models\Country;
use App\Models\Game;
use App\Models\MatchesJobLog;
use App\Models\MatchJobLog;
use App\Models\Odd;
use App\Models\PredictionJobLog;
use App\Models\Season;
use App\Models\SeasonJobLog;
use App\Models\Standing;
use App\Models\StandingJobLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function stats()
    {
        sleep(1);
        $activeStatusId = activeStatusId();

        $results = [
            'countries' => $this->getCountriesStats(Country::class),
            'competitions' => $this->getModelStats(Competition::class, $activeStatusId),
            'odds_enabled_competitions' => $this->getOddsEnabledStats(Competition::class, $activeStatusId),
            'seasons' => $this->getModelStats(Season::class, $activeStatusId),
            'standings' => $this->getModelStats(Standing::class, $activeStatusId),
            'teams' => $this->getModelStats(Team::class, $activeStatusId),
            'matches' => $this->getMatchesStats(),
            'predictions' => $this->getPredictionsStats(Game::class, $activeStatusId),
            'odds' => $this->getOddsStats(Odd::class, $activeStatusId),
        ];

        return response(['results' => $results]);
    }

    public function advancedStats()
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

            'predictions_job_logs' => $predictionsJobLogs,
            'advanced_matches' => $matches,
            'betting_tips_statistics' => [
                'all' => BettingTipsStatistic::where('range', 'Last 1 year')->orderby('gain', 'desc')->get(),
                'totals' => BettingTipsStatistic::where('range', 'Last 1 year')->selectRaw('SUM(gain) as total_gain, SUM(total) as total_totals, SUM(won) as total_won, ROUND(AVG(roi)) as average_roi')->first(),
            ],
        ];

        return response(['results' => $results]);
    }

    private function getModelStats($modelClass, $activeStatusId)
    {
        return [
            'totals' => $modelClass::count(),
            'active' => $modelClass::where('status_id', $activeStatusId)->count(),
            'inactive' => $modelClass::where('status_id', '!=', $activeStatusId)->count(),
        ];
    }

    private function getCountriesStats($modelClass)
    {
        return [
            'totals' => $modelClass::count(),
            'with_competitions' => $modelClass::where('has_competitions', true)->count(),
            'without_competitions' => $modelClass::where('has_competitions', false)->count(),
        ];
    }

    private function getOddsEnabledStats($modelClass, $activeStatusId)
    {
        return [
            'totals' => $modelClass::where('is_odds_enabled', true)->count(),
            'active' => $modelClass::where('is_odds_enabled', true)->where('status_id', $activeStatusId)->count(),
            'inactive' => $modelClass::where('is_odds_enabled', true)->where('status_id', '!=', $activeStatusId)->count(),
        ];
    }

    private function getMatchStatsQuery($date = null)
    {
        $query = Game::query();

        if ($date) {
            $query->whereDate('utc_date', $date);
        }

        return $query;
    }

    private function getMatchesStats($date = null)
    {
        return [
            'totals' => $this->getMatchStatsQuery($date)->count(),
            'past' => $this->getMatchStatsQuery($date)->where('utc_date', '<=', now())->count(),
            'upcoming' => $this->getMatchStatsQuery($date)->where('utc_date', '>', now())->count(),
        ];
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

    private function getOddsStats($modelClass, $activeStatusId)
    {
        return [
            'totals' => $modelClass::whereHas('game')->count(),
            'past' => $modelClass::whereHas('game')->where('utc_date', '<=', now())->where('status_id', $activeStatusId)->count(),
            'upcoming' => $modelClass::whereHas('game')->where('utc_date', '>', now())->where('status_id', $activeStatusId)->count(),
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

    private function getPredictionJobLogsStats($date)
    {
        $selects = 'SUM(job_run_counts) as total_job_run_counts, SUM(competition_run_counts) as total_competition_run_counts, SUM(prediction_run_counts) as total_fetch_run_counts, SUM(prediction_success_counts) as total_fetch_success_counts, SUM(prediction_failed_counts) as total_fetch_failed_counts, SUM(predicted_counts) as total_updated_items_counts';

        return [
            'all' => PredictionJobLog::selectRaw($selects)->first(),
            'today' => PredictionJobLog::whereDate('date', $date)->selectRaw($selects)->first(),
        ];
    }
}
