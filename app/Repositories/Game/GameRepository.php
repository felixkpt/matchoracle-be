<?php

namespace App\Repositories\Game;

use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameRepository implements GameRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Game $model)
    {
    }

    public function index($id = null)
    {

        $seasons = null;
        if (isset(request()->season)) {
            $seasons = Season::where("start_date", 'like', request()->season . '-%')
                ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
                ->get()->pluck('id');
        }

        $now = Carbon::now();

        $competitions = $this->model::with(['competition', 'home_team', 'away_team', 'score'])
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when($seasons, fn ($q) => $q->whereIn('season_id', $seasons))
            ->when(request()->type, fn ($q) => request()->type == 'played' ? $q->where('utc_date', '<', $now) : (request()->type == 'upcoming' ? $q->where('utc_date', '>=', $now) :  true))
            ->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when((request()->year && request()->month), function ($q) {
                return $q
                    ->whereYear('utc_date', request()->year)
                    ->whereMonth('utc_date', request()->month);
            })->when((request()->year && request()->month && request()->date), function ($q) {
                return $q
                    ->whereYear('utc_date', request()->year)
                    ->whereMonth('utc_date', request()->month)
                    ->whereDay('utc_date', request()->date);
            })->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/matches/';
        $results = SearchRepo::of($competitions, ['id', 'name'])
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status'])
            ->orderby('priority_number');

        $results = false ? $results->first() : $results->paginate();

        return response(['results' => $results]);
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

    public function yearMonthDate($year, $month, $date)
    {
        request()->merge(['year' => $year, 'month' => $month, 'date' => $date]);
        return $this->index();
    }

    public function store(Request $request, $data)
    {
        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }
}
