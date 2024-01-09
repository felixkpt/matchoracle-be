<?php

namespace App\Http\Controllers\Admin\Statistics;

use App\Http\Controllers\Controller;
use App\Repositories\Statistics\CompetitionStatisticsRepositoryInterface;

class CompetitionsStatisticsController extends Controller
{

    function __construct(
        private CompetitionStatisticsRepositoryInterface $competitionStatisticsRepositoryInterface
    ) {
    }

    function index($competition_id = null)
    {
        request()->merge(['competition_id' => $competition_id]);
        return $this->competitionStatisticsRepositoryInterface->index();
    }
    
    function store()
    {
        return $this->competitionStatisticsRepositoryInterface->store();
    }
}
