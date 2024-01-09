<?php

namespace App\Utilities;

use App\Models\Game;
use App\Models\GamePredictionType;
use App\Repositories\GameComposer;
use App\Repositories\SearchRepo;
use Illuminate\Support\Carbon;

/**
 * Class GameUtility
 * 
 * This class provides utility functions related to games.
 */
class GameUtility
{

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

        if (request()->prediction_type) {
            $this->setPredictorOptions();
        }

        request()->merge(['order_by' => $order_by ?? 'utc_date', 'per_page' => $per_page]);
        request()->merge(['order_direction' => $order_direction]);

        $games = Game::with(['competition' => fn ($q) => $q->with(['country', 'currentSeason']), 'homeTeam', 'awayTeam', 'score', 'votes', 'referees', 'prediction', 'odds'])
            ->when(request()->show_predictions, fn ($q) => $q->whereHas('prediction'))
            ->when($season_id, fn ($q) => $q->where('season_id', $season_id))
            ->when($team_id, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', $team_id)->orWhere('away_team_id', $team_id)))
            ->when($team_ids, fn ($q) => $this->teamsMatch($q, $team_ids, $playing))
            ->when($currentground, fn ($q) => $currentground == 'home' ? $q->where('home_team_id', $team_id) : ($currentground == 'away' ? $q->where('away_team_id', $team_id) :  $q))
            ->when($from_date, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse($from_date)->format('Y-m-d')))
            ->when($to_date, fn ($q) => $q->whereDate('utc_date', request()->before_to_date ? '<' : '<=', Carbon::parse($to_date)->format('Y-m-d')))
            ->when($date, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse($date)->format('Y-m-d')))
            ->when(!$date && !$to_date && $type, fn ($q) => $this->typeOrdering($q, $type, $to_date))
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when(request()->year && request()->month, fn ($q) => $this->yearMonthFilter($q))
            ->when(request()->year && request()->month && request()->day, fn ($q) => $this->yearMonthDayFilter($q))
            ->when($id, fn ($q) => $q->where('games.id', $id))
            ->when(request()->limit, fn ($q) => $q->limit(request()->limit));

        return $games;
    }

    private function setPredictorOptions()
    {
        $prediction_type = GamePredictionType::where('name', request()->prediction_type)->first();
        if ($prediction_type) {
            preg_match_all('/\d+/', $prediction_type->name, $matches);
            $result = $matches[0];

            request()->merge([
                'history_limit_per_match' => $result[0],
                'current_ground_limit_per_match' => $result[1],
                'h2h_limit_per_match' => $result[2],
            ]);
        }
    }

    private function typeOrdering($q, $type, $to_date)
    {
        $to_date = $to_date ?? Carbon::now();
        $type == 'past' ? $q->where('utc_date', '<', $to_date) : ($type == 'upcoming' ? $q->where('utc_date', '>=', Carbon::now()) :  $q);
    }

    private function yearMonthFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month);
    }

    private function yearMonthDayFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month)->whereDay('utc_date', request()->day);
    }

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

        $uri = '/admin/matches/';

        $results = SearchRepo::of($games, ['id', 'home_team.name', 'away_team.name'])
            ->addColumnWhen(!request()->is_predictor, 'ID', fn ($q) => '<a class="dropdown-item autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . '#' . $q->id . '</a>')
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

            ->addColumnWhen(request()->break_preds, 'Game', fn ($q) => '<a class="dropdown-item autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . $q->homeTeam->name . ' vs ' . $q->awayTeam->name . '</a>', 'cs')
            ->addColumnWhen(request()->break_preds, '1X2', fn ($q) => $this->format1X2(clone $q))
            ->addColumnWhen(request()->break_preds, 'Pick', fn ($q) => $this->formatHda(clone $q))
            ->addColumnWhen(request()->break_preds, 'BTS', fn ($q) => $this->formatBTS(clone $q))
            ->addColumnWhen(request()->break_preds, 'Over25', fn ($q) => $this->formatGoals(clone $q))
            ->addColumnWhen(request()->break_preds, 'CS', fn ($q) => $this->formatCS(clone $q))
            ->addColumnWhen(request()->break_preds, 'Halftime', fn ($q) => $this->formatHTScores(clone $q))
            ->addColumnWhen(request()->break_preds, 'Fulltime', fn ($q) => $this->formatFTScores(clone $q))

            ->addColumnWhen(!request()->is_predictor, 'formatted_prediction', fn ($q) => $this->format_pred(clone $q))
            ->addColumnWhen(!request()->is_predictor, 'current_user_votes', fn ($q) => $this->currentUserVotes($q))
            ->addColumnWhen(!request()->is_predictor, 'Created_at', 'Created_at')
            ->addColumnWhen(!request()->is_predictor, 'Last_fetch', fn ($q) => Carbon::parse($q->last_fetch)->diffForHumans())
            ->addColumnWhen(!request()->is_predictor, 'Predicted', fn ($q) => $q->prediction ? Carbon::parse($q->prediction->created_at)->diffForHumans() : 'N/A')
            ->addColumnWhen(!request()->is_predictor, 'Created_by', 'getUser')
            ->addColumnWhen(!request()->is_predictor, 'Status', 'getStatus')

            ->addActionColumnWhen(!request()->is_predictor, 'action', $uri, 'native', !!request()->is_predictor)
            ->htmls(['Status', 'ID', 'Game', '1X2', 'Pick', 'BTS', 'Over25', 'CS', 'Halftime', 'Fulltime']);

        if (!request()->order_by)
            $results = $results->orderby('utc_date', request()->type == 'upcoming' ? 'asc' : 'desc');

        return $results;
    }

    private function single_pred($q, $col = null)
    {

        if ($q->prediction) {
            $pred = $this->format_pred($q->prediction);

            if ($col == 'hda_proba')
                return $pred->home_win_proba . '%, ' . $pred->draw_proba . '%, ' . $pred->away_win_proba . '%';
            if ($col == 'hda')
                return $pred->Hda;
            if ($col == 'bts')
                return $pred->Bts;
            if ($col == 'over25')
                return $pred->Over25;
            if ($col == 'cs')
                return $pred->Cs;

            return $pred;
        }

        return '-';
    }

    private function format_pred($pred)
    {
        $cs = array_search($pred->cs, scores());
        $pred->Hda = $pred->hda == 0 ? '1' : ($pred->hda == 1 ? 'X' : '2');
        $pred->Bts = $pred->bts == 1 ? 'YES' : 'NO';
        $pred->Over25 = $pred->over25 == 1 ? 'OV' : 'UN';
        $pred->Cs = $cs;
        return $pred;
    }

    private function format1X2($q)
    {
        $class = 'border-start text-dark';

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $this->single_pred(clone $q, 'hda_proba') . '</div>';
    }

    private function formatHda($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::winningSide($q, true);

        $class = 'bg-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->hda == $res) {
                $class = 'border-bottom bg-success text-white';
            } elseif ($pred) {
                $class = 'border-bottom border-danger text-danger';
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'hda') . '</div>';
    }

    private function formatBTS($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::bts($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->bts == $res) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger ';
            }
        }

        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'bts') . '</div>';
    }

    private function formatGoals($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::goals($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->over25 && $res > 2) {
                $class = 'border-bottom border-success';
            } elseif (!$pred->over25 && $res <= 2) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger';
            }
        }

        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'over25') . '</div>';
    }

    private function formatCS($q)
    {

        $has_res = GameComposer::hasResults($q);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            $res = GameComposer::cs($q);

            if ($res) {
                $class = 'border-bottom border-success';
            }
        }

        return '<div class="border-2 py-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'cs') . '</div>';
    }

    private function formatHTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->half_time . '</div>';
    }

    private function formatFTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->full_time . '</div>';
    }

    private function currentUserVotes($q)
    {
        return $q->votes->where(function ($q) {
            return $q->where('user_id', auth()->id())->orWhere('user_ip', request()->ip());
        })->first();
    }
}
