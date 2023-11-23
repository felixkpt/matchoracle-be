<?php

namespace  App\Repositories\GamePrediction;

use App\Repositories\CommonRepoActionsInterface;

interface GamePredictionRepositoryInterface extends CommonRepoActionsInterface
{
    function storePredictions();
    function storeCompetitionScoreTargetOutcome();
}
