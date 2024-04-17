<?php

namespace App\Repositories\Competition\CompetitionAbbreviation;

use App\Http\Controllers\Admin\Odds\OddsController;
use App\Http\Controllers\Admin\Statistics\CompetitionsPredictionsStatisticsController;
use App\Http\Controllers\Admin\Statistics\CompetitionsStatisticsController;
use App\Http\Controllers\Admin\Teams\TeamsController;
use App\Models\CompetitionAbbreviation;
use App\Models\CompetitionPredictionStatistic;
use App\Models\CompetitionStatistic;
use App\Models\GameSource;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompetitionAbbreviationRepository implements CompetitionAbbreviationRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CompetitionAbbreviation $model)
    {
    }

    public function index()
    {

        $statuses = $this->model::query()->with(['country', 'competition']);

        if (request()->all == '1')
            return response(['results' => $statuses->get()]);

        $uri = '/admin/competitions/competition-abbreviations/';
        $statuses = SearchRepo::of($statuses, ['id', 'name'])
            ->addColumn('Is_intl', fn ($q) => $q->is_international ? 'Yes' : 'No')
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Created_by', 'getUser')
            ->addColumn('Status', 'getStatus')
            ->addColumn('action', fn ($q) => call_user_func('actionLinks', $q, $uri, 'modal', 'modal', 'update-competition-abbreviation'))
            ->htmls(['Status'])
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
