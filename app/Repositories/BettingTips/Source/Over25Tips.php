<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class Over25Tips
{
    use BettingTipsTrait;

    public $outcome_name = 'over_25';
    public $odds_name = 'over_25_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'over25_proba';
    private $proba_threshold = 68;

    private $proba_name2 = 'ft_away_win_proba';
    private $proba_threshold2 = 50;

    
}
