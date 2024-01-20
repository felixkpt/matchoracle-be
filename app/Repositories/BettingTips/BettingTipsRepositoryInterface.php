<?php

namespace  App\Repositories\BettingTips;

use App\Repositories\CommonRepoActionsInterface;

interface BettingTipsRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();
    public function today();
    public function yesterday();
    public function tomorrow();
    public function year($year);
    public function yearMonth($year, $month);
    public function yearMonthDay($year, $month, $date);
    public function dateRange($from_date, $to_date);
    public function stats();
}
