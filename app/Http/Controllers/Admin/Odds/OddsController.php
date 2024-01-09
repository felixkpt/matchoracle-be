<?php

namespace App\Http\Controllers\Admin\Odds;

use App\Http\Controllers\Controller;
use App\Repositories\Odds\OddsRepositoryInterface;
use Illuminate\Support\Carbon;

class OddsController extends Controller
{

    public function __construct(protected OddsRepositoryInterface $oddsRepositoryInterface)
    {
    }

    public function index()
    {
        return $this->oddsRepositoryInterface->index();
    }

    function today()
    {
        return $this->oddsRepositoryInterface->today();
    }

    function yesterday()
    {
        return $this->oddsRepositoryInterface->yesterday();
    }

    function tomorrow()
    {
        return $this->oddsRepositoryInterface->tomorrow();
    }

    function year($year)
    {
        return $this->oddsRepositoryInterface->year($year);
    }

    function yearMonth($year, $month)
    {
        return $this->oddsRepositoryInterface->yearMonth($year, $month);
    }

    function yearMonthDay($year, $month, $date)
    {
        return $this->oddsRepositoryInterface->yearMonthDay($year, $month, $date);
    }

    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        $predictions = $this->oddsRepositoryInterface->dateRange($from_date, $to_date);

        return $predictions;
    }
}
