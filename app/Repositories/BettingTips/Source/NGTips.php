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
}
