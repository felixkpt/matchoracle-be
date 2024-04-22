<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class HomeWinTips
{
    use BettingTipsTrait;

    public $outcome_name = 'home_win';
    public $odds_name = 'home_win_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'ft_home_win_proba';
    private $proba_threshold = 60;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 40;
}
