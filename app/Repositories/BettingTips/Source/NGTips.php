<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class NGTips
{
    use BettingTipsTrait;

    private $outcome = 'ng';
    private $odds_name = 'ng_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ng_proba';
    private $proba_threshold = 68;

    private $proba_name2 = 'under25_proba';
    private $proba_threshold2 = 55;

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
}
