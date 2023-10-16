<?php

namespace App\Repositories\Competition;

use App\Http\Controllers\Admin\Teams\TeamsController;
use App\Models\Competition;
use App\Models\GameSource;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\GameSources\FootballDataStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompetitionRepository implements CompetitionRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Competition $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new FootballDataStrategy());
    }

    public function index($single = false, $id = null)
    {

        $competitions = $this->model::with(['continent', 'country', 'currentSeason', 'seasons' => fn ($q) => $q->select(['id', 'competition_id', 'start_date', 'end_date', 'current_matchday', 'winner_id']), 'stages', 'gameSources'])
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when($id, fn ($q) => $q->where('id', $id));

        // Format the data as needed
        $result = [
            // 'country' => $competition->country,
            // 'id' => $competition->id,
            // 'name' => $competition->name,
            // 'code' => $competition->code,
            // 'type' => $competition->type,
            // 'emblem' => $competition->emblem,
            // 'season' => $competition->currentSeason,
            // 'lastUpdated' => $competition->lastUpdated,
        ];

        $uri = '/admin/competitions/';
        $results = SearchRepo::of($competitions, ['id', 'name', 'code', 'country.name', 'seasons.start_date', 'slug'])
            ->addColumn('season', fn ($q) => $q->currentSeason ? (Carbon::parse($q->currentSeason->start_date)->format('Y') . '/' . Carbon::parse($q->currentSeason->end_date)->format('Y')) : null)
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('Emblem', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->emblem ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addColumn('Has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addActionItem(
                [
                    'title' => 'Add Sources',
                    'action' => ['title' => 'add-sources', 'modal' => 'add-sources', 'native' => null, 'use' => 'modal']
                ],
                'Status update'
            )
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Emblem'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->addFillable('has_teams', 'has_teams', ['input' => 'select'])
            ->orderby('priority_number');

        $results = $single ? $results->first() : $results->paginate();

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

    public function storeFromSource(Request $request, $data)
    {

        foreach ($data as $key => $item) {

            $subscription_expires = $item['subscription_expires'];
            $is_subscribed = $subscription_expires === 'never';
            if (!$is_subscribed && $subscription_expires) {
                $subscription_expires = Carbon::parse($subscription_expires)->format('Y-m-d H:i:s');
                $is_subscribed = Carbon::parse($subscription_expires)->isFuture();
            }

            $arr = ['uri' => $item['uri'], 'source_id' => $item['source_id'], 'competition_game_source.subscription_expires' => $subscription_expires, 'competition_game_source.is_subscribed' => $is_subscribed];

            // Get competitions from the selected game source
            $competitions = $this->sourceContext->competitions();

            return $competitions->updateOrCreate($arr);
        }
    }

    function fetchStandings($id)
    {
        $competitions = $this->sourceContext->competitions();

        return $competitions->findStandingsByCompetition($id);
    }

    public function show($id)
    {
        return $this->index(true, $id);
    }

    function addSources(Request $request, $data)
    {
        $competition = $this->model::find($request->id);

        foreach ($data as $key => $item) {

            $subscription_expires = $item['subscription_expires'];
            $is_subscribed = $subscription_expires === 'never';
            if (!$is_subscribed && $subscription_expires) {
                $subscription_expires = Carbon::parse($subscription_expires)->format('Y-m-d H:i:s');
                $is_subscribed = Carbon::parse($subscription_expires)->isFuture();
            }

            $arr = ['uri' => $item['uri'], 'source_id' => $item['source_id'], 'competition_game_source.subscription_expires' => $subscription_expires, 'competition_game_source.is_subscribed' => $is_subscribed];

            // Check if $item (URI && source_id) is not null before proceeding
            if ($item['uri'] || $item['source_id']) {
                // Check if the game source with the given ID doesn't exist
                if (!$competition->gameSources()->where('game_source_id', $key)->exists()) {
                    // Attach the relationship with the URI & or source_id
                    $competition->gameSources()->attach($key, $arr);
                } else {
                    $competition->gameSources()->where('game_source_id', $key)->update($arr);
                }
            } else {
                // Detach the relationship if URI & source_id are null
                $competition->gameSources()->detach($key);
            }
        }

        return response(['type' => 'success', 'message' => "Sources for {$competition->name} updated successfully"]);
    }

    function listSources($id)
    {
        $competition = $this->model::with(['gameSources'])->findOrFail($id);

        $gamesources = GameSource::whereIn('id', $competition->gameSources->pluck('id'));

        $uri = '/admin/countries/';
        $res = SearchRepo::of($gamesources, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->orderBy('name')
            ->paginate();

        return response(['results' => $res]);
    }

    public function seasons($id)
    {
        $competition = $this->model::with(['seasons'])->findOrFail($id);

        return response(['results' => $competition->seasons]);
    }

    public function standings($id, $season_id = null)
    {

        if (!$season_id) {
            $season_id = $this->model::find($id)->currentSeason->id ?? 0;
        }

        request()->merge(['season_id' => $season_id]);

        $query = $this->model::with(['standings.standingTable.team']);

        if ($season_id) {
            $query = $query->whereHas('seasons', function ($query) use ($season_id) {
                $query->where('id', $season_id);
            });
        }

        $competition = $query->findOrFail($id);

        return response(['results' => $competition]);
    }

    function teams($id)
    {

        return app(TeamsController::class)->index($id);
    }
}
