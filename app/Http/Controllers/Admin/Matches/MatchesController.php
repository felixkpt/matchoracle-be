<?php

namespace App\Http\Controllers\Admin\Matches;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MatchesController extends Controller
{

    use CommonMethods;

    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
    ) {
        $this->repo = $gameRepositoryInterface;
    }

    function index($competition_id = null)
    {
        request()->merge(["competition_id" => $competition_id]);

        return $this->gameRepositoryInterface->index();
    }

    function today($competition_id = null)
    {
        request()->merge(["competition_id" => $competition_id]);

        return $this->gameRepositoryInterface->today();
    }

    function yesterday($competition_id = null)
    {
        request()->merge(["competition_id" => $competition_id]);

        return $this->gameRepositoryInterface->yesterday();
    }

    function tomorrow($competition_id = null)
    {
        request()->merge(["competition_id" => $competition_id]);

        return $this->gameRepositoryInterface->tomorrow();
    }

    function year($year)
    {
        return $this->gameRepositoryInterface->year($year);
    }

    function yearMonth($year, $month)
    {
        return $this->gameRepositoryInterface->yearMonth($year, $month);
    }

    function yearMonthDay($year, $month, $date)
    {
        return $this->gameRepositoryInterface->yearMonthDay($year, $month, $date);
    }

    public function dateRange($start_year, $start_month, $start_day, $end_year, $end_month, $end_day)
    {
        $from_date = Carbon::create($start_year, $start_month, $start_day);
        $to_date = Carbon::create($end_year, $end_month, $end_day);

        $predictions = $this->gameRepositoryInterface->dateRange($from_date, $to_date);

        return $predictions;
    }

    function store(Request $request)
    {
        $data = [];
        return $this->gameRepositoryInterface->store($request, $data);
    }
}
