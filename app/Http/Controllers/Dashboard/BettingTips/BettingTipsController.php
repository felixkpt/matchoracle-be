<?php

namespace App\Http\Controllers\Dashboard\BettingTips;

use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\BettingTips\BettingTipsRepositoryInterface;
use App\Services\Validations\BettingTips\BettingTipsValidationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BettingTipsController extends Controller
{

    use CommonControllerMethods;

    function __construct(
        private BettingTipsRepositoryInterface $bettingTipsRepositoryInterface,
        private BettingTipsValidationInterface $repoValidation,
    ) {
        $this->repo = $bettingTipsRepositoryInterface;
        request()->merge(['break_preds' => app()->runningInConsole() ? false : true]);
    }

    function index()
    {
        return $this->repo->index();
    }

    function today()
    {
        return $this->repo->today();
    }

    function yesterday()
    {
        return $this->repo->yesterday();
    }

    function tomorrow()
    {
        return $this->repo->tomorrow();
    }

    function year($year)
    {
        return $this->repo->year($year);
    }

    function yearMonth($year, $month)
    {
        return $this->repo->yearMonth($year, $month);
    }

    function yearMonthDay($year, $month, $date)
    {
        return $this->repo->yearMonthDay($year, $month, $date);
    }

    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        $predictions = $this->repo->dateRange($from_date, $to_date);

        return $predictions;
    }

    function stats()
    {
        return $this->repo->stats();
    }

    public function subscribe(Request $request)
    {
        $data = $this->repoValidation->subscribe($request);
        return $this->repo->subscribe($data);
    }
}
