<?php

namespace App\Utilities;

use App\Models\Game;
use App\Repositories\GameComposer;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class GameUtility
 * 
 * This class provides utility functions related to games.
 */
class GameUtility
{
    use FormatPredictionTrait;

    protected $predictionTypeMode;

    function __construct()
    {
        // Determine the prediction type mode based on the request
        $this->predictionTypeMode = request()->prediction_mode_id == 2 ? 'sourcePrediction' : 'prediction';

        // Configure execution settings
        $this->configureExecutionSettings();
    }

    /**
     * Configure execution settings such as max execution time and memory limit.
     */
    private function configureExecutionSettings()
    {
        ini_set('max_execution_time', 60 * 10);
        ini_set('memory_limit', '1024M');
    }

    /**
     * Apply filters to the games query.
     */
    function applyGameFilters($id = null)
    {

        $all_params = request()->all_params;

        if (is_array($all_params)) {
            $team_id = $all_params['team_id'] ?? null;
            $team_ids = $all_params['team_ids'] ?? null;
            $playing = $all_params['playing'] ?? null;
            $from_date = $all_params['from_date'] ?? null;
            $date = $all_params['date'] ?? null;
            $to_date = $all_params['to_date'] ?? null;
            $currentground = $all_params['currentground'] ?? null;
            $season_id = $all_params['season_id'] ?? null;
            $type = $all_params['type'] ?? null;
            $order_by = $all_params['order_by'] ?? 'utc_date';
            $order_direction = $all_params['order_direction'] ?? 'desc';
            $per_page = $all_params['per_page'] ?? null;
        } else {
            $team_id = request()->team_id ?? null;
            $team_ids = request()->team_ids ?? null;
            $playing = request()->playing ?? null;
            $from_date = request()->from_date ?? null;
            $date = request()->date ?? null;
            $to_date = request()->to_date ?? null;
            $currentground = request()->currentground ?? null;
            $season_id = request()->season_id ?? null;
            $type = request()->type ?? null;
            $order_by = request()->order_by ?? null;
            $order_direction = request()->order_direction ?? 'desc';
            $per_page = request()->per_page ?? null;
        }

        request()->merge(['order_by' => $order_by ?? 'utc_date', 'per_page' => $per_page]);
        request()->merge(['order_direction' => $order_direction]);

        $games = Game::query()
            ->when(true, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when($team_id, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', $team_id)->orWhere('away_team_id', $team_id)))
            ->when($team_ids, fn ($q) => $this->teamsMatch($q, $team_ids, $playing))
            ->when($currentground, fn ($q) => $currentground == 'home' ? $q->where('home_team_id', $team_id) : ($currentground == 'away' ? $q->where('away_team_id', $team_id) :  $q))
            ->when($from_date, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse($from_date)->format('Y-m-d')))
            ->when($to_date, fn ($q) => $q->whereDate('utc_date', request()->before_to_date ? '<' : '<=', Carbon::parse($to_date)->format('Y-m-d')))
            ->when($date, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse($date)->format('Y-m-d')))
            ->when(!$date && !$to_date && $type, fn ($q) => $this->typeOrdering($q, $type, $to_date))
            ->when($season_id, fn ($q) => $q->where('season_id', $season_id))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when(request()->year && request()->month, fn ($q) => $this->yearMonthFilter($q))
            ->when(request()->year && request()->month && request()->day, fn ($q) => $this->yearMonthDayFilter($q))
            ->when($id, fn ($q) => $q->where('games.id', $id))
            ->when(request()->include_ids, fn ($q) => $q->whereIn('games.id', request()->include_ids))
            ->when(request()->exclude_ids, fn ($q) => $q->whereNotIn('games.id', request()->exclude_ids))
            ->when(request()->limit, fn ($q) => $q->limit(request()->limit));

        $with = ['competition' => fn ($q) => $q->with(['country', 'currentSeason']), 'homeTeam', 'awayTeam', 'score', 'votes', 'referees', 'odds'];

        if (request()->prediction_mode_id == 1) {
            array_push($with, 'prediction');
            $games = $games->whereHas('prediction');
        } else if (request()->prediction_mode_id == 2) {
            array_push($with, 'sourcePrediction');
            $games = $games->whereHas('sourcePrediction');
        }

        $games = $games->with($with);

        return $games;
    }

    /**
     * Helper function to order games by type.
     */
    private function typeOrdering($q, $type, $to_date)
    {
        $to_date = $to_date ?? Carbon::now();
        $type == 'past' ? $q->where('utc_date', '<', $to_date) : ($type == 'upcoming' ? $q->where('utc_date', '>=', Carbon::now()) :  $q);
    }

    /**
     * Helper function to filter games by year and month.
     */
    private function yearMonthFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month);
    }

    /**
     * Helper function to filter games by year, month, and day.
     */
    private function yearMonthDayFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month)->whereDay('utc_date', request()->day);
    }

    /**
     * Helper function to filter games by team IDs.
     */
    private function teamsMatch($q, $team_ids, $playing)
    {
        return $q->where(function ($q) use ($team_ids, $playing) {
            [$home_team_id, $away_team_id] = $team_ids;
            $arr = [['home_team_id', $home_team_id], ['away_team_id', $away_team_id]];
            $arr2 = [['home_team_id', $away_team_id], ['away_team_id', $home_team_id]];

            if ($playing == 'home-away') {
                $q->where($arr);
            } else {
                $q->where($arr)->orWhere($arr2);
            }
        });
    }

    /**
     * Format retrieved games.
     *
     * @param mixed $games The games to format.
     * @return mixed The formatted games.
     */
    function formatGames($games)
    {
        $homeWinVotes = function ($q) {
            return $q->votes->where('winner', 'home')->count();
        };

        $drawVotes = function ($q) {
            return $q->votes->where('winner', 'draw')->count();
        };

        $awayWinVotes = function ($q) {
            return $q->votes->where('winner', 'away')->count();
        };

        $overVotes = function ($q) {
            return $q->votes->where('over_under', 'over')->count();
        };

        $underVotes = function ($q) {
            return $q->votes->where('over_under', 'under')->count();
        };

        $ggVotes = function ($q) {
            return $q->votes->where('bts', 'gg')->count();
        };

        $ngVotes = function ($q) {
            return $q->votes->where('bts', 'ng')->count();
        };

        $uri = '/dashboard/matches/';

        $search_builder = null;
        $joiners = [' vs ', ' v '];
        if (request()->search && Str::contains(request()->search, $joiners)) {
            $search = array_values(array_filter(explode($joiners[0], request()->search)));
            if (count($search) !== 2) {
                $search = array_values(array_filter(explode($joiners[1], request()->search)));
            }

            if (count($search) === 2) {

                $search_builder = function ($q) use ($search) {
                    $q->where(function ($q) use ($search) {
                        $this->searchBuilderHelper($q, $search)('homeTeam', 'awayTeam');
                    })->orWhere(function ($q) use ($search) {
                        $this->searchBuilderHelper($q, $search)('awayTeam', 'homeTeam');
                    });
                };
            }
        }

        $uri = '/dashboard/matches/';
        $results = SearchRepo::of($games, ['id', 'home_team.name', 'away_team.name'], $search_builder)
            ->setModelUri($uri)
            ->addColumn('Winner', fn ($q) => $q->score ? GameComposer::winningSide($q) : null)
            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Competition', fn ($q) => $q->competition->name)

            ->addColumn('winningSideHT', fn ($q) => $q->score ? GameComposer::winningSideHT($q) : null)
            ->addColumn('hasResultsHT', fn ($q) => $q->score ? GameComposer::hasResultsHT($q) : null)

            ->addColumn('winningSide', fn ($q) => $q->score ? GameComposer::winningSide($q) : null)
            ->addColumn('hasResults', fn ($q) => $q->score ? GameComposer::hasResults($q) : null)
            ->addColumn('BTS', fn ($q) => $q->score ? GameComposer::bts($q, true) : null)
            ->addColumn('Goals', fn ($q) => $q->score ? GameComposer::goals($q, true) : null)

            ->addColumn('is_future', fn ($q) => Carbon::parse($q->utc_date)->isFuture())
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('home_win_votes', $homeWinVotes)
            ->addColumn('draw_votes', $drawVotes)
            ->addColumn('away_win_votes', $awayWinVotes)
            ->addColumn('over_votes', $overVotes)
            ->addColumn('under_votes', $underVotes)
            ->addColumn('gg_votes', $ggVotes)
            ->addColumn('ng_votes', $ngVotes)

            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'prediction_strategy', fn ($q) => $this->prediction_strategy(clone $q))

            ->addColumnWhen(!request()->is_predictor, 'current_user_votes', fn ($q) => $this->currentUserVotes($q))
            ->addColumnWhen(!request()->is_predictor, 'Created_by', 'getUser')
            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Created_at', 'Created_at')
            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Last_fetch', fn ($q) => Carbon::parse($q->last_fetch)->diffForHumans())
            ->addColumnWhen((!request()->is_predictor && !request()->without_response),
                'Predicted',
                function ($q) {
                    if (request()->prediction_mode_id == 2) {
                        return 'N/A';
                    }
                    return $q->prediction ? Carbon::parse($q->prediction->created_at)->diffForHumans() : 'N/A';
                }
            )
            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Status', 'getStatus');

        if (!request()->order_by)
            $results = $results->orderby('utc_date', request()->type == 'upcoming' ? 'asc' : 'desc');

        return $results;
    }

    /**
     * Helper function to build search queries.
     */
    private function searchBuilderHelper($q, $search)
    {
        return function ($team1, $team2) use ($q, $search) {
            $q->whereHas($team1, function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search[0] . '%');
            })->whereHas($team2, function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search[1] . '%');
            });
        };
    }

    /**
     * Helper function to retrieve current user votes for a game.
     */
    private function currentUserVotes($q)
    {
        return $q->votes->where(function ($q) {
            return $q->where('user_id', auth()->id())->orWhere('user_ip', request()->ip());
        })->first();
    }
}
