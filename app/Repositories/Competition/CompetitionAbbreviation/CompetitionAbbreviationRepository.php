<?php

namespace App\Repositories\Competition\CompetitionAbbreviation;

use App\Models\CompetitionAbbreviation;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;

class CompetitionAbbreviationRepository implements CompetitionAbbreviationRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CompetitionAbbreviation $model) {}

    public function index()
    {

        $statuses = $this->model::query()->with(['country', 'competition']);

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        $uri = '/dashboard/competitions/competition-abbreviations/';
        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->fillable(['competition_id', 'name', 'is_international'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Is_intl', fn($q) => $q->is_international ? 'Yes' : 'No')
            ->paginate();

        return response(['results' => $statuses]);
    }

    public function store(Request $request, $data)
    {

        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';

        return response(['type' => 'success', 'message' => 'Competition Abbreviation ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        $status = $this->model::findOrFail($id);
        return response(['results' => $status]);
    }
}
