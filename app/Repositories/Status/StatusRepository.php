<?php

namespace App\Repositories\Status;

use App\Models\Status;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StatusRepository implements StatusRepositoryInterface
{
    use CommonRepoActions;

    private $checked_permissions = [];

    function __construct(protected Status $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query();

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        $uri = '/admin/settings/picklists/statuses/default/';
        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
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

        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
