<?php

namespace App\Repositories\BettingTips;

use App\Models\Game;
use App\Repositories\BettingTips\Core\AwayWinTips;
use App\Repositories\BettingTips\Core\DrawTips;
use App\Repositories\BettingTips\Core\GGTips;
use App\Repositories\BettingTips\Core\HomeWinTips;
use App\Repositories\BettingTips\Core\NGTips;
use App\Repositories\BettingTips\Core\Over25Tips;
use App\Repositories\BettingTips\Core\Under25Tips;
use App\Repositories\CommonRepoActions;

class BettingTipsRepository implements BettingTipsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Game $model)
    {
    }

    public function index()
    {
        $results = $this->getTipResults(request()->type);

        $arr = ['results' => $results];
        if (request()->without_response) {
            return $arr;
        }

        return response($arr);
    }

    protected function getTipResults($tipType)
    {
        $tipClass = $this->getTipClass($tipType);

        if (request()->multiples) {
            return $tipClass->multiples();
        } else {
            return $tipClass->singles();
        }
    }

    protected function getTipClass($tipType)
    {
        switch ($tipType) {
            case 'home_win_tips':
                return new HomeWinTips();
            case 'away_win_tips':
                return new AwayWinTips();
            case 'draw_tips':
                return new DrawTips();
            case 'gg_tips':
                return new GGTips();
            case 'ng_tips':
                return new NGTips();
            case 'over_25_tips':
                return new Over25Tips();
            case 'under_25_tips':
                return new Under25Tips();
            default:
                abort(404, 'Tip type not found');
        }
    }


    public function today()
    {
        request()->merge(['today' => true]);
        return $this->index();
    }

    public function yesterday()
    {
        request()->merge(['yesterday' => true]);
        return $this->index();
    }

    public function tomorrow()
    {
        request()->merge(['tomorrow' => true]);
        return $this->index();
    }

    public function year($year)
    {
        request()->merge(['year' => $year]);
        return $this->index();
    }

    public function yearMonth($year, $month)
    {
        request()->merge(['year' => $year, 'month' => $month]);
        return $this->index();
    }

    public function yearMonthDay($year, $month, $day)
    {
        request()->merge(['year' => $year, 'month' => $month, 'day' => $day]);
        return $this->index();
    }

    public function dateRange($from_date, $to_date)
    {
        request()->merge(['from_date' => $from_date, 'to_date' => $to_date]);
        return $this->index();
    }

    public function show($id)
    {
        return $this->index($id);
    }
}
