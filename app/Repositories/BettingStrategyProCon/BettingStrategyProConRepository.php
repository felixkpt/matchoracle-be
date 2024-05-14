<?php

namespace App\Repositories\BettingStrategyProCon;

use App\Models\BettingStrategyProCon;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;

class BettingStrategyProConRepository implements BettingStrategyProConRepositoryInterface
{
    use CommonRepoActions;

    function __construct(protected BettingStrategyProCon $model)
    {
    }

    public function index()
    {

        $betting_strategies_pro_cons = $this->model::query();

        if (request()->all == '1')
            return response(['results' => $betting_strategies_pro_cons->get()]);

        $uri = '/dashboard/settings/picklists/betting-strategies-pro-cons/';
        $betting_strategies_pro_cons = SearchRepo::of($betting_strategies_pro_cons, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal', 'update-status'))
            ->htmls(['Icon'])
            ->paginate();

        return response(['results' => $betting_strategies_pro_cons]);
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
