<?php

namespace App\Repositories\Competition\PredictionLog;

use App\Repositories\CommonRepoActionsInterface;

interface CompetitionPredictionLogRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();
    
    public function show($id);

}
