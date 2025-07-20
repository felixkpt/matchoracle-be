<?php

namespace App\Repositories\GameSource;

use App\Models\GameSource;
use App\Models\Status;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;

class GameSourceRepository implements GameSourceRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected GameSource $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query();

        if (request()->all == '1')
            $statuses = $statuses->where('status_id', Status::where('name', 'active')->first()->id);

        $uri = '/dashboard/settings/picklists/game-sources/';
        $results = SearchRepo::of($statuses, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->paginate();

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

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
