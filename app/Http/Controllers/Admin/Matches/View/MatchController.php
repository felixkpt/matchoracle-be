<?php

namespace App\Http\Controllers\Admin\Matchs\View;

use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;

class MatchController extends Controller
{

    public function __construct(
        private GameRepositoryInterface $competitionRepositoryInterface,
    ) {
    }

  
}
