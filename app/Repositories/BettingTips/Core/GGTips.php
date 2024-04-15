<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class GGTips
{
    use BettingTipsTrait;

    private $outcome = 'gg';
    private $odds_name = 'gg_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'gg_proba';
    private $proba_threshold = 58;

    private $proba_name2 = 'ft_home_win_proba';
    private $proba_threshold2 = 37;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_gg_preds_true_percentage', '>=', 48);
    }
}
