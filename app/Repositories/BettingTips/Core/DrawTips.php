<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;
use App\Utilities\GameUtility;
use Illuminate\Support\Carbon;

class DrawTips
{
    use BettingTipsTrait;

    private $odds_name = 'draw_odds';
    private $odds_min_threshold = 1.5;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ft_draw_proba';
    private $proba_threshold = 37;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 44;

    private $multiples_combined_min_odds = 5;

    function singles()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q))
            ->whereHas('competition.predictionStatistic', fn ($q) => $this->predictionStatisticFilter($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold)->where($this->proba_name2, '>=', $this->proba_threshold2));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'draw'));

        $investment = $this->singlesInvestment($results);

        $results = $results->paginate(request()->per_page ?? 50);

        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()->whereHas('odds', fn ($q) => $this->oddsRange($q))
            ->whereHas('competition.predictionStatistic', fn ($q) => $this->predictionStatisticFilter($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'draw'));

        $investment = $this->multiplesInvestment($results);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function predictionStatisticFilter($q)
    {
        if (!request()->show_source_predictions) {
            $q->where('full_time_draws_preds_true_percentage', '>=', 20);
        }
    }
}