<?php

namespace App\Repositories\BettingStrategy;

use App\Models\BettingStrategy;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class BettingStrategyRepository implements BettingStrategyRepositoryInterface
{
    use CommonRepoActions;

    function __construct(protected BettingStrategy $model)
    {
    }

    public function index()
    {

        $betting_strategies = $this->model::query();

        if (request()->all == '1')
            return response(['results' => $betting_strategies->get()]);

        $uri = '/dashboard/settings/picklists/betting-strategies/';
        $betting_strategies = SearchRepo::of($betting_strategies, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal', 'update-status'))
            ->htmls(['Icon'])
            ->paginate();

        return response(['results' => $betting_strategies]);
    }

    public function store(Request $request, $data)
    {

        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
