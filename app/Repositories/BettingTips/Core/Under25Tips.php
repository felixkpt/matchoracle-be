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
    private $proba_threshold = 55;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 35;

    function singles()
    {
        $results = $this->getGames();

        $investment = $this->singlesInvestment($results);

        $results = $results->paginate(request()->per_page ?? 50);

        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $results = $this->getGames();

        $investment = $this->multiplesInvestment($results);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function predictionStatisticFilter($q)
    {
        $q->where('ft_under25_preds_true_percentage', '>=', 30);
    }
}
