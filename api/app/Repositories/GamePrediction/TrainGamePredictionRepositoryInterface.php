<?php

namespace  App\Repositories\GamePrediction;

use App\Repositories\CommonRepoActionsInterface;

interface TrainGamePredictionRepositoryInterface extends CommonRepoActionsInterface
{
    function raw();
    function storeCompetitionPredictionTypeStatistics();
    function updateCompetitionLastTraining();
}
