<?php

namespace App\Repositories\Season;

use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\GameSources\FootballDataStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;

class SeasonRepository implements SeasonRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Season $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new FootballDataStrategy());
    }

    public function index($id = null)
    {

        $seasons = $this->model::with(['competition', 'winner'])
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->id, fn ($q) => $q->where('id', request()->id))
            ->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/seasons/';
        $results = SearchRepo::of($seasons, ['start_date'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status'])
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
