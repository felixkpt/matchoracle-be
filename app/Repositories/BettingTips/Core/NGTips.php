<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class NGTips
{
    use BettingTipsTrait;

    private $outcome = 'ng';
    private $odds_name = 'ng_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ng_proba';
    private $proba_threshold = 55;

    private $proba_name2 = 'under25_proba';
    private $proba_threshold2 = 46;

    function predictionStatisticFilter($q)
    {
        $q->where('ft_ng_preds_true_percentage', '>=', 50);
    }
}
