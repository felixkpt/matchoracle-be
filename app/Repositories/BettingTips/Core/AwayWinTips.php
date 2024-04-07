<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class AwayWinTips
{
    use BettingTipsTrait;

    private $outcome = 'away_win';
    private $odds_name = 'away_win_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ft_away_win_proba';
    private $proba_threshold = 44;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 40;

    private $multiples_combined_min_odds = 5;

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
        $q->where('ft_away_wins_preds_true_percentage', '>=', 40);
    }
}
