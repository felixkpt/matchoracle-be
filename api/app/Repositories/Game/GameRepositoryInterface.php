<?php

namespace  App\Repositories\Game;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface GameRepositoryInterface extends CommonRepoActionsInterface
{

    public function index($id = null, $without_response = null);
    public function today();
    public function yesterday();
    public function tomorrow();
    public function year($year);
    public function yearMonth($year, $month);
    public function yearMonthDay($year, $month, $date);
    public function dateRange($from_date, $to_date);
    public function store(Request $request, $data);
    public function show($id);
    public function vote($id, $data);
    public function updateGame($id);
}
