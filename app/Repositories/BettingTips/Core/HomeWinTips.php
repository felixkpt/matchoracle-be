<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class HomeWinTips
{
    use BettingTipsTrait;

    function __construct()
    {
        $this->setTipsProperties(self::class);
    }

    /**
     * Filter method for prediction statistics.
     *
     * @param object $q The query builder object.
     * @return void
     */
    function predictionStatisticFilter($q)
    {
        // Apply filter based on the percentage of true predictions for the specific outcome
        $q->where('ft_home_wins_preds_true_percentage', '>=', 45);
    }
}
