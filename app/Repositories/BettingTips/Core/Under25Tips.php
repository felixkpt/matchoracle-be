<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class Under25Tips
{
    use BettingTipsTrait;

    private $outcome = 'under_25';
    private $odds_name = 'under_25_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'under25_proba';
    private $proba_threshold = 60;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 45;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_under25_preds_true_percentage', '>=', 30);
    }
}
