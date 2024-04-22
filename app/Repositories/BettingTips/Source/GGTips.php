<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class GGTips
{
    use BettingTipsTrait;

    private $outcome = 'gg';
    private $odds_name = 'gg_odds';
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 5.0;

    private $proba_name = 'gg_proba';
    private $proba_threshold = 70;

    private $proba_name2 = 'over25_proba';
    private $proba_threshold2 = 60;
}
