<?php

namespace App\Repositories\Odds;

use App\Models\Odd;
use App\Models\Status;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OddsRepository implements OddsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Odd $model)
    {
    }

    public function index($id = null)
    {
        $from_date = request()->from_date ?? null;
        $date = request()->date ?? null;
        $to_date = request()->to_date ?? null;

        $odds = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn ($q) => $q->whereHas('game', fn ($q) => $q->where('competition_id', request()->competition_id)->when(request()->season_id, fn ($q) => $q->where('season_id', request()->season_id))))
            ->when($from_date, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse($from_date)->format('Y-m-d')))
            ->when($to_date, fn ($q) => $q->whereDate('utc_date', request()->before_to_date ? '<' : '<=', Carbon::parse($to_date)->format('Y-m-d')))
            ->when($date, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse($date)->format('Y-m-d')))->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when(request()->year && request()->month, fn ($q) => $this->yearMonthFilter($q))
            ->when(request()->year && request()->month && request()->day, fn ($q) => $this->yearMonthDayFilter($q));

        if ($this->applyFiltersOnly) return $odds;

        $uri = '/admin/odds/';
        $results = SearchRepo::of($odds, ['id', 'home_team', 'away_team'])
            ->addColumn('Date', fn ($q) => Carbon::parse($q->utc_date)->format('Y-m-d'))
            ->addColumn('home_win', fn ($q) => $q->home_win_odds ?? '-')
            ->addColumn('draw', fn ($q) => $q->draw_odds ?? '-')
            ->addColumn('away_win', fn ($q) => $q->away_win_odds ?? '-')
            ->addColumn('over_25', fn ($q) => $q->over_25_odds ?? '-')
            ->addColumn('under_25', fn ($q) => $q->under_25_odds ?? '-')
            ->addColumn('GG', fn ($q) => $q->gg_odds ?? '-')
            ->addColumn('NG', fn ($q) => $q->ng_odds ?? '-')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Game', fn ($q) => $q->game->id ? '<a class="dropdown-item autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->game->id . '" href="/admin/matches/view/' . $q->game->id . '">#' . $q->game->id . '</a>' :  'N/A')
            ->addActionColumn('action', $uri)
            ->htmls(['Status', 'Game'])
            ->orderBy('utc_date', 'desc')
            ->paginate();

        return response(['results' => $results]);
    }

    private function yearMonthFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month);
    }

    private function yearMonthDayFilter($q)
    {
        return $q->whereYear('utc_date', request()->year)->whereMonth('utc_date', request()->month)->whereDay('utc_date', request()->day);
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

    public function store(Request $request, $data)
    {
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
