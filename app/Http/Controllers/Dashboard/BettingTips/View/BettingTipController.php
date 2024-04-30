<?php

namespace App\Http\Controllers\Dashboard\BettingTips\View;

use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;

class BettingTipController extends Controller
{

    public function __construct(
        private GameRepositoryInterface $competitionRepositoryInterface,
    ) {
    }

   
}
