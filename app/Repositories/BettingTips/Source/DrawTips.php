<?php

namespace App\Repositories\BettingTips\Source;

use App\Repositories\BettingTips\BettingTipsTrait;

class DrawTips
{
    use BettingTipsTrait;

    public $outcome_name = 'draw';
    public $odds_name = 'draw_odds';
    private $odds_min_threshold = 1.5;
    private $odds_max_threshold = 6.0;

    private $proba_name = 'ft_draw_proba';
    private $proba_threshold = 49;

    private $proba_name2 = 'ng_proba';
    private $proba_threshold2 = 50;
}
