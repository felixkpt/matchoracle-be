<?php

namespace App\Repositories\BettingTips\Core;

use App\Utilities\GameUtility;
use Illuminate\Support\Carbon;

class DrawTips
{
    use BettingTipsTrait;

    private $odds_name = 'draw_odds';
    private $odds_min_threshold = 1.5;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'draw_proba';
    private $proba_threshold = 37;

    function __construct()
    {
        request()->merge(['to_date' => Carbon::now()->addDays(7)]);
    }

    function singles()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()->whereHas('odds', fn ($q) => $q->where($this->odds_name, '>', 2.4));
        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'draw'));

        $investment = $this->sinlgesInvestment($results);

        $results = $results->paginate(request()->per_page ?? 50);

        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()->whereHas('odds', fn ($q) => $q->where($this->odds_name, '>', 2.4));
        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'draw'));

        $investment = $this->multiplesInvestment($results, 5);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }
}
