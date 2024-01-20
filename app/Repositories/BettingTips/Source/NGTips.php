<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;
use App\Utilities\GameUtility;

class NGTips
{
    use BettingTipsTrait;

    private $odds_name = 'ng_odds';
    private $odds_min_threshold = 1.5;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ng_proba';
    private $proba_threshold = 55;

    private $proba_name2 = 'under25_proba';
    private $proba_threshold2 = 50;

    private $multiples_combined_min_odds = 5;

    function singles()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold)->where($this->proba_name2, '>=', $this->proba_threshold2));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'ng'));

        $investment = $this->singlesInvestment($results);

        $results = $results->paginate(request()->per_page ?? 50);

        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold + 2)->where($this->proba_name2, '>=', $this->proba_threshold2));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'ng'));

        $investment = $this->multiplesInvestment($results);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

}