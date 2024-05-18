<?php

namespace App\Repositories\Competition\PredictionLog;

use App\Models\CompetitionPredictionLog;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;

class CompetitionPredictionLogRepository implements CompetitionPredictionLogRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CompetitionPredictionLog $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query()->with(['competition']);

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        $uri = '/dashboard/competitions/prediction-logs/';
        $statuses = SearchRepo::of($statuses, ['id', 'competition.name', 'competition.country.name', 'date', 'total_games'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Status', 'getStatus')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal'))
            ->htmls(['Status'])
            ->paginate();

        return response(['results' => $statuses]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
