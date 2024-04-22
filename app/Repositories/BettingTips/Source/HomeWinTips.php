<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class HomeWinTips
{
    use BettingTipsTrait;

    private $outcome = 'home_win';
    private $odds_name = 'home_win_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'ft_home_win_proba';
    private $proba_threshold = 60;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 40;
}
