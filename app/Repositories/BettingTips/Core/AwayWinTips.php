<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class AwayWinTips
{
    use BettingTipsTrait;

    public $outcome_name = 'away_win';
    public $odds_name = 'away_win_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'ft_away_win_proba';
    private $proba_threshold = 50;

    private $proba_name2 = 'over25_proba';
    private $proba_threshold2 = 40;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_away_wins_preds_true_percentage', '>=', 45);
    }
}
