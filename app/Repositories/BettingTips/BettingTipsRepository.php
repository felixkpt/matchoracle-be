<?php

namespace App\Repositories\BettingTips;

use App\Models\BettingTipsStatistic;
use App\Models\Game;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Support\Carbon;

class BettingTipsRepository implements BettingTipsRepositoryInterface
{

    use CommonRepoActions;

    protected $predictionModeId;

    function __construct(protected Game $model)
    {
        $this->predictionModeId = request()->prediction_mode_id;

        request()->merge([
            'break_preds' => true,
            'show_predictions' => $this->predictionModeId == 2 ? 2 : 1,
            'prediction_mode_id' => $this->predictionModeId == 2 ? 2 : 1,
        ]);

        if (!request()->from_date) {
            request()->merge(['from_date' => Carbon::now()->subMonths(12 * 3)]);
        }
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

        $tipClass = BettingTipsFactory::create($this->predictionModeId, $tipType);

        if (request()->multiples) {
            return $tipClass->multiples();
        } else {
            return $tipClass->singles();
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

    public function stats()
    {
        $builder = BettingTipsStatistic::query();

        $results = SearchRepo::of($builder, ['type', 'range'])
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Multiples', fn ($q) => $q->is_multiples ? 'Yes' : 'No')
            ->htmls(['Status'])
            ->paginate();

        return response(['results' => $results]);
    }
}
