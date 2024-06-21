<?php

namespace App\Repositories\GameScoreStatus;

use App\Models\GameScoreStatus;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GameScoreStatusRepository implements GameScoreStatusRepositoryInterface
{
    use CommonRepoActions;

    function __construct(protected GameScoreStatus $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()));

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        if ($this->applyFiltersOnly) return $statuses;

        $uri = '/dashboard/settings/picklists/statuses/game-score-statuses/';
        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Icon', function ($q) {
                return '<div class="d-flex align-items-center"><iconify-icon icon="' . $q->icon . '" class="' . $q->class . ' me-1"></iconify-icon>' . Str::ucfirst(Str::replace('_', ' ', $q->name)) . '</div>';
            })
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal', 'update-status'))
            ->htmls(['Icon'])
            ->paginate();

        return response(['results' => $statuses]);
    }

    public function store(Request $request, $data)
    {

        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        return response(['type' => 'success', 'message' => 'GameScoreStatus ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
