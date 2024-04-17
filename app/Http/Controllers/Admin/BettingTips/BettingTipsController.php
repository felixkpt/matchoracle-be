<?php

namespace App\Http\Controllers\Admin\BettingTips;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\BettingTips\BettingTipsRepositoryInterface;
use Illuminate\Support\Carbon;

class BettingTipsController extends Controller
{

    use CommonControllerMethods;

    function __construct(
        private BettingTipsRepositoryInterface $bettingTipsRepositoryInterface,
    ) {
        request()->merge(['break_preds' => app()->runningInConsole() ? false : true]);
    }

    function index()
    {
        return $this->bettingTipsRepositoryInterface->index();
    }

    function today()
    {
        return $this->bettingTipsRepositoryInterface->today();
    }

    function yesterday()
    {
        return $this->bettingTipsRepositoryInterface->yesterday();
    }

    function tomorrow()
    {
        return $this->bettingTipsRepositoryInterface->tomorrow();
    }

    function year($year)
    {
        return $this->bettingTipsRepositoryInterface->year($year);
    }

    function yearMonth($year, $month)
    {
        return $this->bettingTipsRepositoryInterface->yearMonth($year, $month);
    }

    function yearMonthDay($year, $month, $date)
    {
        return $this->bettingTipsRepositoryInterface->yearMonthDay($year, $month, $date);
    }

    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        $predictions = $this->bettingTipsRepositoryInterface->dateRange($from_date, $to_date);

        return $predictions;
    }

    function stats()
    {
        return $this->bettingTipsRepositoryInterface->stats();
    }
}
