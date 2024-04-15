<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class DrawTips
{
    use BettingTipsTrait;

    private $outcome = 'draw';
    private $odds_name = 'draw_odds';
    private $odds_min_threshold = 1.5;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'ft_draw_proba';
    private $proba_threshold = 42;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 42;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_draws_preds_true_percentage', '>=', 22);
    }
}
