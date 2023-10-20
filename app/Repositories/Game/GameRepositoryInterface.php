<?php

namespace  App\Repositories\Game;

use App\Repositories\CommonRepoActionsInterface;
use Illuminate\Http\Request;

interface GameRepositoryInterface extends CommonRepoActionsInterface
{

    public function index();
    public function today();
    public function yesterday();
    public function tomorrow();
    public function year($year);
    public function yearMonth($year, $month);
    public function yearMonthDate($year, $month, $date);
    public function store(Request $request, $data);
}
