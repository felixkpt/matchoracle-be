<?php

namespace  App\Repositories\Statistics;

use App\Repositories\CommonRepoActionsInterface;

interface CompetitionStatisticsRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();

    public function store();

}
