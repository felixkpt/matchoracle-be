<?php

namespace App\Repositories\BettingTips;

use Carbon\Carbon;
use Illuminate\Support\Str;

trait CalculateInvestment
{

    protected $stakeRatio;
    protected $strategy = 'flat';

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
        $this->stakeRatio = $stake_ratio;
        if (request()->betting_strategy_id) {
            $strategies = [1 => 'flat', 2 => 'recovery', 3 => 'martingle'];
            $this->strategy = $strategies[request()->betting_strategy_id];
        }

        $initial_bankroll = $this->initial_bankroll;
        $final_bankroll = $initial_bankroll;
        $bankroll_deposits = [$initial_bankroll];

        $min_odds = $is_singles ? 1 : $this->multiples_combined_min_odds;

        $results = $results->orderBy('utc_date', 'asc')->get(-1)['data'];

        $total = $won = $gain = $total_gains = 0;
        $weekly_report = [];
        $curr_week = null;

        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;

        $odds = 1;
        $total_won_odds = 0;
        $outcome = 'W';
        $betslips = [];
        $betslip = [];

        $weekly_bankroll_deposits = [$initial_bankroll];
        $weekly_wins = 0;

        $prev_stake =  $prev_outcome = null;
        foreach ($results as $game) {

            if ($game['Winner'] == 'POSTPONED') continue;

            if (is_array($all_tips) && isset($game['id'])) {
                // If $type is an array and $game->id exists, search for the game ID in $type
                if (in_array($game['id'], array_column($all_tips, 'id'))) {
                    $tipIndex = array_search($game['id'], array_column($all_tips, 'id'));
                    $odds_name = $all_tips[$tipIndex]['odds_name'];
                    $outcome_name = $all_tips[$tipIndex]['outcome_name'];
                }
            }

            $game_odds = clone ($game['odds']);
            $game_odds = $game_odds[0][$odds_name];
            unset($game['odds']);

            $game['odds'] = $game_odds;
            $game['is_subscribed'] = true;

            $odds *= $game_odds;

            if ($game['outcome'] == 'L') $outcome = 'L';
            else if ($game['outcome'] == 'U' && $outcome != 'L') $outcome = 'U';


            if ($outcome == 'U' && Carbon::parse($game['utc_date'])->isAfter(now()->subDays(3))) {
                $game = [
                    'id' => now()->unix() + rand(1, 10000),
                    'utc_date' => $game['utc_date'],
                    'competition' => [
                        'name' => 'Tornament ?',
                        'logo' => null,
                    ],
                    'home_team' => [
                        'name' => 'Team A ?',
                        'logo' => null,
                    ],
                    'away_team' => [
                        'name' => 'Team B ?',
                        'logo' => null,

                    ],
                    'outcome' => $game['outcome'],
                    'odds' => $game['odds'],
                    'is_subscribed' => false,
                ];
            }

            $betslip[] = [
                'game' => $game,
                'odds_name' => $odds_name,
                'odds_name_print' => $this->formatOutcomeName($outcome_name),
            ];

            if ($odds >= $min_odds) {
                $total++;

                $stake = $this->getStake(
                    $total_gains,
                    $odds,
                    [
                        'current_losing_streak' => $current_losing_streak,
                        'prev_stake' => $prev_stake,
                        'prev_outcome' => $prev_outcome,
                    ]
                );

                $prev_stake = $stake;

                $prev_outcome = $outcome;

                // Check if the current bankroll is sufficient for the stake
                if ($final_bankroll - $stake < 0) {
                    // If not, apply a top-up
                    $required = intval(ceil($stake - $final_bankroll));

                    // $working_bankroll = $initial_bankroll;
                    $bankroll_deposits[] = $required;
                    $weekly_bankroll_deposits[] = $required;
                    $final_bankroll += $required;
                }

                if ($outcome == 'W') {
                    // Accumulate total odds
                    $total_won_odds += $odds;

                    $gain += ($stake * $odds) - $stake;

                    $won++;
                    // Increment weekly wins count too
                    $weekly_wins++;

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


                // workOnWeeklyReport
                $this->workOnWeeklyReport($game, $betslip, $stake, $gain, $curr_week, $weekly_bankroll_deposits, $weekly_wins, $weekly_report);

                $final_bankroll += $gain;

                $deposits = array_reduce($bankroll_deposits, fn ($prev, $curr) => $prev + $curr, 0);
                $deposits_formatted = number_format($deposits, 0, '.', request()->without_response ? '' : ',');;

                $total_gains = $final_bankroll - $deposits;
                $total_gains_formatted = number_format($total_gains, 0, '.', request()->without_response ? '' : ',');

                $final_bankroll_formatted = number_format($final_bankroll, 0, '.', request()->without_response ? '' : ',');

                $betslips[] = [
                    'betslip' => $betslip,
                    'odds' => number_format($odds, 2, '.', ''),
                    'stake' => $stake,
                    'total_gains' => $total_gains_formatted,
                    'bankroll_deposits' => $deposits_formatted,
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
        // end foreach

        // presentation layer

        // Handle the case where all outcomes are wins or losses
        if ($current_winning_streak === count($results) && $current_winning_streak > $longest_winning_streak) {
            $longest_winning_streak = $current_winning_streak;
        } elseif ($current_losing_streak === count($results) && $current_losing_streak > $longest_losing_streak) {
            $longest_losing_streak = $current_losing_streak;
        }

        $total_gains_formatted = number_format($total_gains, 0, '.', request()->without_response ? '' : ',');
        $roi = number_format(($final_bankroll / $initial_bankroll) * 100, 0, '.', request()->without_response ? '' : ',');

        $initial_bankroll_formatted = number_format($initial_bankroll, 0, '.', request()->without_response ? '' : ',');
        $final_bankroll_formatted = number_format($final_bankroll, 0, '.', request()->without_response ? '' : ',');

        $deposits = array_reduce($bankroll_deposits, fn ($prev, $curr) => $prev + $curr, 0);
        $deposits_formatted = number_format($deposits, 0, '.', request()->without_response ? '' : ',');;

        // Calculate average won odds
        $average_won_odds = $won > 0 ? number_format($total_won_odds / $won, 2, '.', '') : 0;

        $weekly_report = array_map(function ($report) {

            $deposits = array_reduce($report['bankroll_deposits'], fn ($prev, $curr) => $prev + $curr, 0);
            $report['bankroll_deposits'] = $deposits;
            return $report;
        }, $weekly_report);

        return [
            'betslips' => array_reverse($betslips),
            'initial_bankroll' => $initial_bankroll_formatted,
            'bankroll_deposits' => $deposits_formatted,
            'final_bankroll' => $final_bankroll_formatted,
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'average_won_odds' => $average_won_odds,
            'total_gains' => $total_gains_formatted,
            'roi' => $roi,
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
            'weekly_report' => $weekly_report,
        ];
    }

    function workOnWeeklyReport($game, $betslip, $stake, $gain, &$curr_week, &$weekly_bankroll_deposits, &$weekly_wins, &$weekly_report)
    {

        if (!$curr_week || Carbon::parse($game['utc_date'])->diffInDays(Carbon::parse($curr_week)) > 7) {
            $curr_week = Carbon::parse($game['utc_date'])->format('Y-m-d');

            $report = [
                'bankroll_deposits' => $weekly_bankroll_deposits,
                'betslip_counts' => 1,
                'tip_counts' => count($betslip),
                'wins' => $weekly_wins,
                'stakes' => round($stake),
                'gains' => round($gain)
            ];

            $weekly_report[$curr_week] = $report;
        } else {

            $report = $weekly_report[$curr_week];

            $weekly_report[$curr_week] =
                [
                    'bankroll_deposits' => array_merge($report['bankroll_deposits'], $weekly_bankroll_deposits),
                    'betslip_counts' => $report['betslip_counts'] + 1,
                    'wins' => $report['wins'] + $weekly_wins,
                    'tip_counts' => $report['tip_counts'] + count($betslip),
                    'stakes' => $report['tip_counts'] + round($stake),
                    'gains' => $report['gains'] + round($gain)
                ];
        }

        // Reset weekly wins count to 0 for the new week
        $weekly_wins = 0;
        $weekly_bankroll_deposits = [];
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

    function formatOutcomeName($outcome_name)
    {
        $name = $outcome_name;
        if ($name == 'gg') return 'GG';
        if ($name == 'ng') return 'NG';

        return Str::ucfirst(Str::replace('_', ' ', $name));
    }

    function getStake($total_gains, $odds, $options = [])
    {

        $current_losing_streak = $options['current_losing_streak'] ?? null;
        $prev_stake = $options['prev_stake'] ?? null;
        $prev_outcome = $options['prev_outcome'] ?? null;
        $initial_stake = round($this->stakeRatio * $this->initial_bankroll, 2);

        $strategy = $this->strategy;

        if ($strategy == 'flat') {

            return $initial_stake;
        } else if ($strategy == 'recovery') {

            $profit_to_make = $this->initial_bankroll * 0.1;

            $tl = 0;
            if ($total_gains < 0) {
                $tl = abs($total_gains);
            }

            if ($current_losing_streak > 8) {
                return $initial_stake;
            } else {
                $stake = round($profit_to_make + ($tl) / ($odds - 1), 2);
                return $stake;
            }
        } else if ($strategy == 'martingle') {

            if ($prev_outcome == 'L' && $prev_stake) {
                return $prev_stake * 2;
            }

            return $initial_stake;
        }
    }
}
