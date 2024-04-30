<?php

namespace App\Http\Controllers\Dashboard\Predictions\View;

use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;

class PredictionController extends Controller
{

    public function __construct(
        private GameRepositoryInterface $competitionRepositoryInterface,
    ) {
    }

   
}
