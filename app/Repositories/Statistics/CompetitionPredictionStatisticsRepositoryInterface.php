<?php

namespace  App\Repositories\Statistics;

use App\Repositories\CommonRepoActionsInterface;

interface CompetitionPredictionStatisticsRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store();

}
