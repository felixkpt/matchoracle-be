<?php

namespace  App\Repositories\GamePrediction;

use App\Repositories\CommonRepoActionsInterface;

interface GamePredictionRepositoryInterface extends CommonRepoActionsInterface
{
    function raw();
    function storePredictions();
    function storeCompetitionScoreTargetOutcome();
    function predictionsJobLogs();
    function updateCompetitionLastTraining();
}
