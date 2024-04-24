<?php

namespace App\Repositories\BettingTips;

use App\Models\Game;
use App\Repositories\GameComposer;
use Illuminate\Support\Str;

trait CalculateInvestment
{

    function singlesInvestment($results, $odds_name = null, $outcome_name = null, $all_tips = null)
    {
        return $this->calculateInvestment($results, $this->singles_stake_ratio, $odds_name, $outcome_name, $all_tips);
    }

    function multiplesInvestment($results, $odds_name = null, $outcome_name = null, $all_tips = null)
    {
        return $this->calculateInvestment($results, $this->multiples_stake_ratio, $odds_name, $outcome_name, $all_tips, false);
    }

    private function calculateInvestment($results, $stake_ratio, $odds_name, $outcome_name, $all_tips, $is_singles = true)
    {
        $initial_bankroll = $this->initial_bankroll;
        $bankroll_deposits = 1;
        $current_bankroll = $initial_bankroll;
        $stake = $stake_ratio * $initial_bankroll;
        $min_odds = $is_singles ? 1 : $this->multiples_combined_min_odds;

        $results = $results->orderBy('utc_date', 'asc')->get(-1)['data'];

        $total = $won = $gain = 0;
        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;
        $total_odds = 0; // Variable to store total odds for multiples

        $odds = 1;
        $outcome = 'W';
        $betslips = [];
        $betslip = [];
        $final_bankroll = $current_bankroll;
        foreach ($results as $game) {

            if (is_array($all_tips) && isset($game['id'])) {
                // If $type is an array and $game->id exists, search for the game ID in $type
                if (in_array($game['id'], array_column($all_tips, 'id'))) {
                    $tipIndex = array_search($game['id'], array_column($all_tips, 'id'));
                    $odds_name = $all_tips[$tipIndex]['odds_name'];
                    $outcome_name = $all_tips[$tipIndex]['outcome_name'];
                }
            }

            if ($game['Winner'] == 'POSTPONED') continue;

            $odds *= $game['odds'][0]->{$odds_name};

            if ($game['outcome'] == 'L') $outcome = 'L';
            else if ($game['outcome'] == 'U' && $outcome != 'L') $outcome = 'U';

            $betslip[] = [
                'game' => $game,
                'odds_name' => $odds_name,
                'odds_name_print' => $this->formatOutcomeName($outcome_name),
            ];

            if ($odds >= $min_odds) {
                $total++;

                // Check if the current bankroll is sufficient for the stake
                if ($current_bankroll - $stake < 0) {
                    // If not, apply a top-up
                    $current_bankroll = $initial_bankroll;
                    $bankroll_deposits++;
                }

                if ($outcome == 'W') {
                    // Accumulate total odds
                    $total_odds += $odds;

                    $gain += ($stake * $odds) - $stake;
                    $won++;

                    // Update streaks
                    $current_winning_streak++;
                    $current_losing_streak = 0;

                    // Update longest winning streak
                    if ($current_winning_streak > $longest_winning_streak) {
                        $longest_winning_streak = $current_winning_streak;
                    }
                } elseif ($outcome == 'L') {
                    $gain -= $stake;

                    // Update streaks
                    $current_losing_streak++;
                    $current_winning_streak = 0;

                    // Update longest losing streak
                    if ($current_losing_streak > $longest_losing_streak) {
                        $longest_losing_streak = $current_losing_streak;
                    }
                }

                $current_bankroll += $gain;
                $final_bankroll = $current_bankroll;
                $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');
                $betslips[] = [
                    'betslip' => $betslip,
                    'odds' => number_format($odds, 2, '.', ''),
                    'stake' => $stake,
                    'bankroll_deposits' => $bankroll_deposits,
                    'final_bankroll' => $final_bankroll_formatted,
                    'outcome' => $outcome,
                ];

                // reset
                $odds = 1;
                $outcome = 'W';
                $betslip = [];
                $gain = 0;
            }
        }

        // Calculate average odds for multiples
        $average_won_odds = $won > 0 ? number_format($total_odds / $won, 2, '.', '') : 0;

        // Handle the case where all outcomes are wins or losses
        if ($current_winning_streak === count($results) && $current_winning_streak > $longest_winning_streak) {
            $longest_winning_streak = $current_winning_streak;
        } elseif ($current_losing_streak === count($results) && $current_losing_streak > $longest_losing_streak) {
            $longest_losing_streak = $current_losing_streak;
        }

        $gain = $final_bankroll - $initial_bankroll;
        $gain = number_format($gain, 2, '.', request()->without_response ? '' : ',');
        $roi = number_format(($final_bankroll / $initial_bankroll) * 100, 0, '.', request()->without_response ? '' : ',');

        $initial_bankroll_formatted = number_format($initial_bankroll, 2, '.', request()->without_response ? '' : ',');
        $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');

        return [
            'betslips' => array_reverse($betslips),
            'initial_bankroll' => $initial_bankroll_formatted,
            'bankroll_deposits' => $bankroll_deposits,
            'final_bankroll' => $final_bankroll_formatted,
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'average_won_odds' => $average_won_odds,
            'gain' => $gain,
            'roi' => $roi,
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
        ];
    }

    private function calculateStreaks($outcomes)
    {
        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;

        foreach ($outcomes as $outcome) {
            if ($outcome == 'W') {
                // Update streaks
                $current_winning_streak++;
                $current_losing_streak = 0;

                // Update longest winning streak
                if ($current_winning_streak > $longest_winning_streak) {
                    $longest_winning_streak = $current_winning_streak;
                }
            } elseif ($outcome == 'L') {
                // Update streaks
                $current_losing_streak++;
                $current_winning_streak = 0;

                // Update longest losing streak
                if ($current_losing_streak > $longest_losing_streak) {
                    $longest_losing_streak = $current_losing_streak;
                }
            }
        }

        return [
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
        ];
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

    function formatOutcomeName($outcome_name)
    {
        $name = $outcome_name;
        if ($name == 'gg') return 'GG';
        if ($name == 'ng') return 'NG';

        return Str::ucfirst(Str::replace('_', ' ', $name));
    }
}
