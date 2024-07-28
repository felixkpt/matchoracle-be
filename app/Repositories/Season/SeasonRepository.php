<?php

namespace App\Repositories\Season;

use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SeasonRepository implements SeasonRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Season $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function index($id = null)
    {
        $seasons = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->with(['competition', 'winner'])
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when($id, fn ($q) => $q->where('id', $id));

        if ($this->applyFiltersOnly) return $seasons;

        $uri = '/dashboard/seasons/';
        $results = SearchRepo::of($seasons, ['start_date'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Winner', fn ($q) => $q->winner->name ?? '-')
            ->addColumn('Played', fn ($q) => '-')
            ->addColumn('Fetched_standings', fn ($q) => $q->fetched_standings ?  'Yes' : 'No')
            ->addColumn('Fetched_all_matches', fn ($q) => $q->fetched_all_matches ?  'Yes' : 'No')
            ->addColumn('Fetched_all_single_matches', fn ($q) => $q->fetched_all_single_matches ?  'Yes' : 'No')
            ->orderby('start_date', 'desc');

        $results = false ? $results->first() : $results->paginate();

        return response(['results' => $results]);
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
