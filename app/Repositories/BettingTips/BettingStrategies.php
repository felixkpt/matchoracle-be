<?php

namespace App\Repositories\BettingTips;

class BettingStrategies
{
    public $initial_bankroll;
    public $initial_stake;
    public $total_gains;
    public $options;
    public $odds;

    function __construct($initial_bankroll, $initial_stake, $total_gains, $odds, $options)
    {
        $this->initial_bankroll = $initial_bankroll;
        $this->initial_stake = $initial_stake;
        $this->total_gains = $total_gains;
        $this->options = $options;
        $this->odds = $odds;
    }

    function flat()
    {
        return $this->initial_stake;
    }
    
    function recovery()
    {
        $current_losing_streak = $this->options['current_losing_streak'] ?? null;

        $profit_to_make = $this->initial_bankroll * 0.1;

        $tl = 0;
        if ($this->total_gains < 0) {
            $tl = abs($this->total_gains);
        }

        if ($current_losing_streak > 5) {
            return $this->initial_stake;
        } else {
            $stake = round($profit_to_make + ($tl) / ($this->odds - 1), 2);
            return $stake;
        }
    }

    function martingle()
    {
        $prev_stake = $this->options['prev_stake'] ?? null;
        $prev_outcome = $this->options['prev_outcome'] ?? null;

        if ($prev_outcome == 'L' && $prev_stake) {
            return $prev_stake * 2;
        }

        return $this->initial_stake;
    }
}
