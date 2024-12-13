<?php

namespace App\Repositories\Competition\CompetitionAbbreviation;

use App\Models\CompetitionAbbreviation;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Carbon\Carbon;
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

        $uri = '/competitions/competition-abbreviations/';
        $statuses = SearchRepo::of($statuses, ['id', 'name', 'country.name'])
            ->fillable(['competition_id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Updated_at', fn($q) => Carbon::parse($q->updated_at)->diffForHumans())
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
