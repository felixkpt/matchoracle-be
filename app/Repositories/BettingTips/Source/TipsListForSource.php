<?php

namespace App\Repositories\BettingTips\Source;

class TipsListForSource
{

    public array $tipClasses = [
        'all_tips' => AllTips::class,
        'home_win_tips' => HomeWinTips::class,
        'away_win_tips' => AwayWinTips::class,
        'draw_tips' => DrawTips::class,
        'gg_tips' => GGTips::class,
        'ng_tips' => NGTips::class,
        'over_25_tips' => Over25Tips::class,
        'under_25_tips' => Under25Tips::class,
    ];
}
