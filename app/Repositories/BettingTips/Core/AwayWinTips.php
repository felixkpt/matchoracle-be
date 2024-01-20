<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;
use App\Utilities\GameUtility;

class AwayWinTips
{
    use BettingTipsTrait;

    private $odds_name = 'away_win_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 3.0;

    private $proba_name = 'ft_away_win_proba';
    private $proba_threshold = 40;

    private $proba_name2 = 'gg_proba';
    private $proba_threshold2 = 44;

    private $multiples_combined_min_odds = 5;

    function singles()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q))
            ->whereHas('competition.predictionStatistic', fn ($q) => $this->predictionStatisticFilter($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold)->where($this->proba_name2, '>=', $this->proba_threshold2));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'away_win'));

        $investment = $this->singlesInvestment($results);

        $results = $results->paginate(request()->per_page ?? 50);

        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q))
            ->whereHas('competition.predictionStatistic', fn ($q) => $this->predictionStatisticFilter($q));

        $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold));
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, 'away_win'));

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
            $q->where('full_time_away_wins_preds_true_percentage', '>=', 50);
        }
    }
}
