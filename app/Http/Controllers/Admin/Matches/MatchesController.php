<?php

namespace App\Http\Controllers\Admin\Matches;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use Illuminate\Http\Request;

class MatchesController extends Controller
{

    use CommonMethods;

    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
    ) {
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

    function store(Request $request)
    {
        $data = [];
        return $this->gameRepositoryInterface->store($request, $data);
    }
}
