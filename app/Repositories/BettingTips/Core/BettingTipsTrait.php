<?php

namespace App\Repositories\BettingTips\Core;

use App\Models\Game;
use App\Repositories\GameComposer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

trait BettingTipsTrait
{

    private $initial_bankroll = 1000;
    private $singles_stake_ratio = 0.1;
    private $multiples_stake_ratio = 0.1;

    private function sinlgesInvestment($results)
    {
        $initial_bankroll = $this->initial_bankroll;
        $stake = $this->singles_stake_ratio * $initial_bankroll;
        $results = $results->orderBy('utc_date', 'desc')->get(-1)['data'];

        $total = $won = $gain = 0;
        foreach ($results as $game) {

            // if (!$this->lastPredsStatePassed($game)) continue;

            $total++;

            $outcome = $game['outcome'];

            if ($outcome == 'W') {
                $gain += ($stake * $game['odds'][0]->{$this->odds_name}) - $stake;
                $won++;
            } elseif ($outcome == 'L') {
                $gain -= $stake;
            }
        }

        $final_bankroll = number_format($initial_bankroll + $gain, 2);

        $gain = number_format($gain, 2);
        $initial_bankroll = number_format($initial_bankroll, 2);

        return [
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'gain' => $gain,
            'initial_bankroll' => $initial_bankroll,
            'final_bankroll' => $final_bankroll
        ];
    }

    private function multiplesInvestment($results, $min_odds = 5)
    {
        $initial_bankroll = $this->initial_bankroll;
        $stake = $this->multiples_stake_ratio * $initial_bankroll;
        $results = $results->orderBy('utc_date', 'asc')->get(-1)['data'];

        $total = $won = $gain = 0;
        $odds = 1;
        $outcome = 'W';
        $betslips = [];
        $betslip = [];
        foreach ($results as $game) {

            $odds *= $game['odds'][0]->{$this->odds_name};
            $betslip[] = $game;

            if ($game['outcome'] == 'L') $outcome = 'L';
            else if ($game['outcome'] == 'U' && $outcome != 'L') $outcome = 'U';

            if ($odds >= $min_odds) {
                $total++;

                if ($outcome == 'W') {
                    $gain += ($stake * $odds) - $stake;
                    $won++;
                } elseif ($outcome == 'L') {
                    $gain -= $stake;
                }

                $betslips[] = [
                    'betslip' => $betslip,
                    'odds' => number_format($odds, 2, '.', ''),
                    'outcome' => $outcome,
                ];
                // reset
                $odds = 1;
                $outcome = 'W';
                $betslip = [];
            }
        }

        $final_bankroll = number_format($initial_bankroll + $gain, 2);

        $gain = number_format($gain, 2);
        $initial_bankroll = number_format($initial_bankroll, 2);

        return [
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'gain' => $gain,
            'initial_bankroll' => $initial_bankroll,
            'final_bankroll' => $final_bankroll,
            'betslips' => array_reverse($betslips)
        ];
    }

    private function getOutcome($game, $type)
    {

        if (!GameComposer::hasResults($game)) return 'U';

        if ($type == 'home_win') {
            return GameComposer::winningSide($game, true) == 0 ? 'W' : 'L';
        } elseif ($type == 'draw') {
            return GameComposer::winningSide($game, true) == 1 ? 'W' : 'L';
        } elseif ($type == 'away_win') {
            return GameComposer::winningSide($game, true) == 2 ? 'W' : 'L';
        } elseif ($type == 'gg') {
            return GameComposer::bts($game, true) ? 'W' : 'L';
        } elseif ($type == 'ng') {
            return !GameComposer::bts($game, true) ? 'W' : 'L';
        } elseif ($type == 'over_25') {
            return GameComposer::goals($game, true) > 2 ? 'W' : 'L';
        } elseif ($type == 'under_25') {
            return GameComposer::goals($game, true) <= 2 ? 'W' : 'L';
        }

        return 'U';
    }

    private function lastPredsStatePassed($game)
    {
        $home_team_prev_games = Game::with(['score'])->where('home_team_id', $game->home_team_id)->where('utc_date', '<', Carbon::today())->whereHas('prediction')->whereHas('score', fn ($q) => $q->whereNotNull('home_scores_full_time'))->take(3)->orderBy('utc_date', 'desc')->get();
        $away_team_prev_games = Game::with(['score'])->where('away_team_id', $game->away_team_id)->where('utc_date', '<', Carbon::today())->whereHas('prediction')->whereHas('score', fn ($q) => $q->whereNotNull('home_scores_full_time'))->take(3)->orderBy('utc_date', 'desc')->get();

        $home_team_correct_preds = 0;
        foreach ($home_team_prev_games as $game) {
            $pred = $game->prediction;
            $hda = GameComposer::winningSide($game, true);
            if ($pred->hda == $hda) $home_team_correct_preds++;
        }

        $away_team_correct_preds = 0;
        foreach ($away_team_prev_games as $game) {
            $pred = $game->prediction;
            $hda = GameComposer::winningSide($game, true);
            if ($pred->hda == $hda) $away_team_correct_preds++;
        }

        return $home_team_correct_preds > 0 && $away_team_correct_preds > 0;
    }

    function oddsRange($q)
    {
        $q->where($this->odds_name, '>=', $this->odds_min_threshold)->where($this->odds_name, '<=', $this->odds_max_threshold);
    }

    private function paginate($results, $perPage)
    {
        $perPage = $perPage ?? request()->per_page ?? 50;
        $page = request()->page ?? 1;

        $offset = ($page - 1) * $perPage;

        $items = array_slice($results, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            count($results),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
