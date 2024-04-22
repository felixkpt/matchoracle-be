<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class Under25Tips
{
    use BettingTipsTrait;

    public $outcome_name = 'under_25';
    public $odds_name = 'under_25_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'under25_proba';
    private $proba_threshold = 60;

    private $proba_name2 = 'ft_draw_proba';
    private $proba_threshold2 = 34;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_under25_preds_true_percentage', '>=', 58);
    }
}
