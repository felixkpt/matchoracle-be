<?php

namespace App\Http\Controllers\Admin\Predictions;

use App\Http\Controllers\CommonMethods;
use App\Http\Controllers\Controller;
use App\Repositories\Game\GameRepositoryInterface;
use Illuminate\Http\Request;

class PredictionsController extends Controller
{

    use CommonMethods;
    
    function __construct(
        private GameRepositoryInterface $gameRepositoryInterface,
    ) {
    }

    function index()
    {
        return $this->gameRepositoryInterface->index();
    }

    function today()
    {
        return $this->gameRepositoryInterface->today();
    }

    function yesterday()
    {
        return $this->gameRepositoryInterface->yesterday();
    }

    function tomorrow()
    {
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

    function yearMonthDate($year, $month, $date)
    {
        return $this->gameRepositoryInterface->yearMonthDate($year, $month, $date);
    }

    function store(Request $request)
    {
        $data = [];
        return $this->gameRepositoryInterface->store($request, $data);
    }
}
