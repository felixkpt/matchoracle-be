<?php

namespace App\Repositories\BettingTips\Core;

class TipsListForCore
{

    public array $tipClasses = [
        'home_win_tips' => HomeWinTips::class,
        'away_win_tips' => AwayWinTips::class,
        'draw_tips' => DrawTips::class,
        'gg_tips' => GGTips::class,
        'ng_tips' => NGTips::class,
        'over_25_tips' => Over25Tips::class,
        'under_25_tips' => Under25Tips::class,
    ];
}
