<?php

namespace App\Utilities;

use App\Models\Game;
use App\Repositories\GameComposer;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Support\Carbon;
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
        $this->predictionTypeMode = request()->prediction_mode_id == 2 ? 'sourcePrediction' : 'prediction';
        $this->configureExecutionSettings();
    }

    private function configureExecutionSettings()
    {
        ini_set('max_execution_time', 60 * 10);
        ini_set('memory_limit', '1024M');
    }

    function applyGameFilters($id = null)
    {
        $all_params = request()->all_params;

        $params = is_array($all_params) ? $all_params : request()->all();
        $order_by = $params['order_by'] ?? 'utc_date';
        $order_direction = $params['order_direction'] ?? 'desc';
        $per_page = $params['per_page'] ?? null;

        request()->merge(['order_by' => $order_by, 'per_page' => $per_page, 'order_direction' => $order_direction]);

        $games = Game::query()
            ->when(true, fn ($q) => $q->where('status_id', activeStatusId()));

        // Apply filters
        $this->applyTeamFilters($games, $params);
        $this->applyDateFilters($games, $params);
        $this->applyMiscFilters($games, $params, $id);

        $games = $this->applyWithRelations($games, $params);

        return $games;
    }

    private function applyTeamFilters($query, $params)
    {
        $query->when($params['team_id'] ?? null, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', $params['team_id'])->orWhere('away_team_id', $params['team_id'])))
            ->when($params['team_ids'] ?? null, fn ($q) => $this->teamsMatch($q, $params['team_ids'], $params['playing'] ?? null))
            ->when($params['currentground'] ?? null, fn ($q) => $this->filterByGround($q, $params['currentground'], $params['team_id']));
    }

    private function applyDateFilters($query, $params)
    {
        $query->when($params['from_date'] ?? null, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse($params['from_date'])->format('Y-m-d')))
            ->when($params['to_date'] ?? null, fn ($q) => $q->whereDate('utc_date', request()->before_to_date ? '<' : '<=', Carbon::parse($params['to_date'])->format('Y-m-d')))
            ->when($params['date'] ?? null, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse($params['date'])->format('Y-m-d')))
            ->when(!($params['date'] ?? null) && !($params['to_date'] ?? null) && ($params['type'] ?? null), fn ($q) => $this->typeOrdering($q, $params['type'], $params['to_date'] ?? null));
    }

    private function applyMiscFilters($query, $params, $id)
    {
        $query->when($params['season_id'] ?? null, fn ($q) => $q->where('season_id', $params['season_id']))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when($id, fn ($q) => $q->where('games.id', $id))
            ->when(request()->include_ids, fn ($q) => $q->whereIn('games.id', request()->include_ids))
            ->when(request()->exclude_ids, fn ($q) => $q->whereNotIn('games.id', request()->exclude_ids))
            ->when(request()->limit, fn ($q) => $q->limit(request()->limit));
    }

    private function applyWithRelations($query, $params)
    {
        $with = [
            'competition' => fn ($q) => $q->with(['country', 'currentSeason']),
            'homeTeam', 'awayTeam', 'score', 'votes', 'referees', 'odds'
        ];

        if (request()->include_preds || request()->requires_preds) {
            $with[] = $this->predictionTypeMode;
            $query->when(request()->requires_preds, fn ($q) => $q->whereHas($this->predictionTypeMode));
        }

        return $query->with($with);
    }

    private function filterByGround($query, $currentground, $team_id)
    {
        return $currentground == 'home' ? $query->where('home_team_id', $team_id) : ($currentground == 'away' ? $query->where('away_team_id', $team_id) : $query);
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
            ->addColumn('is_future', fn ($q) => Carbon::parse($q->utc_date)->isFuture())

            ->addColumn('Winner', fn ($q) => $q->score ? GameComposer::winningSide($q) : null)
            ->addColumn('winningSideHT', fn ($q) => $q->score ? GameComposer::winningSideHT($q, true) : null)
            ->addColumn('hasResultsHT', fn ($q) => $q->score ? GameComposer::hasResultsHT($q, true) : null)
            ->addColumn('winningSideFT', fn ($q) => $q->score ? GameComposer::winningSide($q, true) : null)
            ->addColumn('hasResultsFT', fn ($q) => $q->score ? GameComposer::hasResults($q, true) : null)

            ->addColumn('BTS', fn ($q) => $q->score ? GameComposer::bts($q, true) : null)
            ->addColumn('goalsCount', fn ($q) => $q->score ? GameComposer::goals($q, true) : null)
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('home_win_votes', $homeWinVotes)
            ->addColumn('draw_votes', $drawVotes)
            ->addColumn('away_win_votes', $awayWinVotes)
            ->addColumn('over_votes', $overVotes)
            ->addColumn('under_votes', $underVotes)
            ->addColumn('gg_votes', $ggVotes)
            ->addColumn('ng_votes', $ngVotes)

            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Updated_at', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumnWhen(!request()->is_predictor, 'current_user_votes', fn ($q) => $this->currentUserVotes($q))
            ->addColumnWhen(!request()->is_predictor, 'Created_by', 'getUser')
            ->addColumnWhen((!request()->is_predictor && !request()->without_response), 'Competition', fn ($q) => $q->competition->name)
            ->addColumnWhen((!request()->is_predictor && !request()->without_response && request()->include_preds), ['prediction_strategy', 'Predicted'], fn ($q) => $this->prediction_strategy(clone $q));

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
