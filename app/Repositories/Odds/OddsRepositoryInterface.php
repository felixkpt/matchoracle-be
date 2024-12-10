<?php

namespace  App\Repositories\Odds;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface OddsRepositoryInterface extends CommonRepoActionsInterface
{

    public function index($id = null);
    public function today();
    public function yesterday();
    public function tomorrow();
    public function upcoming();
    public function year($year);
    public function yearMonth($year, $month);
    public function yearMonthDay($year, $month, $date);
    public function dateRange($from_date, $to_date);
    public function store(Request $request, $data);
    public function show($id);
}
