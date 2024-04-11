<?php

namespace App\Repositories\Competition;

use App\Http\Controllers\Admin\Odds\OddsController;
use App\Http\Controllers\Admin\Statistics\CompetitionsPredictionsStatisticsController;
use App\Http\Controllers\Admin\Statistics\CompetitionsStatisticsController;
use App\Http\Controllers\Admin\Teams\TeamsController;
use App\Models\Competition;
use App\Models\CompetitionPredictionStatistic;
use App\Models\CompetitionStatistic;
use App\Models\GameSource;
use App\Models\Odd;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompetitionRepository implements CompetitionRepositoryInterface
{

    use CommonRepoActions;

    protected GameSourceStrategy $sourceContext;

    function __construct(protected Competition $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function index($single = false, $id = null)
    {

        $competitions = $this->model::with([
            'lastAction',
            'continent', 'country', 'currentSeason',
            'seasons' => fn ($q) => $q->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
                ->select(['id', 'competition_id', 'start_date', 'end_date', 'current_matchday', 'winner_id']),
            'stages', 'gameSources',

        ])
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->is_odds_enabled == 1, fn ($q) => $q->where('is_odds_enabled', true))
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/competitions/';
        $results = SearchRepo::of($competitions, ['id', 'name', 'code', 'country.name', 'seasons.start_date', 'slug'])
            ->addColumn('season', fn ($q) => $q->currentSeason ? (Carbon::parse($q->currentSeason->start_date)->format('Y') . '/' . Carbon::parse($q->currentSeason->end_date)->format('Y')) : null)
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addColumn('Logo', fn ($q) => '<a class="autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ? asset($q->logo) : asset('assets/images/competitions/default_logo.png')) . '" /></a>')
            ->addColumn('Has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addColumn('seasons_fetched', function ($q) {
                $item = $q->lastAction->seasons_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('standings_fetched', function ($q) {
                $item = $q->lastAction->standings_recent_results_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('p_matches_fetched', function ($q) {
                $item = $q->lastAction->matches_recent_results_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('u_matches_fetched', function ($q) {
                $item = $q->lastAction->matches_fixtures_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })->addColumn('Predictions_last_train', function ($q) {
                $item = $q->lastAction->predictions_last_train ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })            
            ->addColumn('odds', fn ($q) => Odd::whereHas('game', fn ($qry) => $qry->where('competition_id', $q->id))->count())
            ->addActionItem(
                [
                    'title' => 'Add Sources',
                    'action' => ['title' => 'add-sources', 'modal' => 'add-sources', 'native' => null, 'use' => 'modal']
                ],
                'Update status'
            )
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Logo'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->addFillable('has_teams', 'has_teams', ['input' => 'select'])
            ->orderby('id');

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

            $arr = ['source_uri' => $item['source_uri'], 'source_id' => $item['source_id'], 'competition_game_source.subscription_expires' => $subscription_expires, 'competition_game_source.is_subscribed' => $is_subscribed];

            // Get competitions from the selected game source
            $competitions = $this->sourceContext->competitions();

            return $competitions->updateOrCreate($arr);
        }
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

            $arr = ['source_uri' => $item['source_uri'], 'source_id' => $item['source_id'], 'competition_game_source.subscription_expires' => $subscription_expires, 'competition_game_source.is_subscribed' => $is_subscribed];

            // Check if $item (URI && source_id) is not null before proceeding
            if ($item['source_uri'] || $item['source_id']) {
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

    function fetchSeasons($id, $data)
    {

        $competition = $this->model::findOrFail($id);

        $seasonsHandler = $this->sourceContext->seasonsHandler();

        return $seasonsHandler->fetchSeasons($competition->id);
    }

    function fetchStandings($id, $data)
    {
        $season_id = $data['season_id'];

        $competition = $this->model::findOrFail($id);

        $standingsHandler = $this->sourceContext->standingsHandler();

        return $standingsHandler->fetchStandings($competition->id, $season_id);
    }

    function fetchMatches($id, $data)
    {
        $season_id = $data['season_id'];
        $is_fixtures = !!request()->is_fixtures;

        $competition = $this->model::findOrFail($id);

        $matchesHandler = $this->sourceContext->matchesHandler();

        return $matchesHandler->fetchMatches($competition->id, $season_id, $is_fixtures);
    }

    function listSources($id)
    {
        $competition = $this->model::with(['gameSources'])->findOrFail($id);

        $gamesources = GameSource::whereIn('id', $competition->gameSources->pluck('id'));

        $uri = '/admin/countries/';
        $res = SearchRepo::of($gamesources, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->orderBy('name')
            ->paginate();

        return response(['results' => $res]);
    }

    public function seasons($id)
    {

        $seasons = Season::where('competition_id', $id)->with(['competition', 'winner']);

        $uri = '/admin/countries/';
        $res = SearchRepo::of($seasons, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'getStatus')
            ->addActionColumn('action', $uri)
            ->htmls(['Status'])
            ->orderBy('start_date')
            ->paginate();

        return response(['results' => $res]);
    }

    public function standings($id, $season_id = null)
    {

        if (!$season_id) {
            $season_id = $this->model::find($id)->currentSeason->id ?? 0;
        }

        request()->merge(['season_id' => $season_id]);

        $query = $this->model::with(['standings.standingTable.team', 'standings.season']);

        if ($season_id) {
            $query = $query->whereHas('seasons', function ($query) use ($season_id) {
                $query->where('id', $season_id);
            });
        }

        $competition = $query->findOrFail($id);

        $arr = ['results' => $competition];

        if (request()->without_response) return $arr;
        return response($arr);
    }

    function teams($id)
    {
        return app(TeamsController::class)->index($id);
    }

    function odds($id)
    {
        request()->merge(['competition_id' => $id]);

        return app(OddsController::class)->index();
    }

    function statistics($id)
    {
        return app(CompetitionsStatisticsController::class)->index($id);
    }

    function predictionStatistics($id)
    {
        return app(CompetitionsPredictionsStatisticsController::class)->index($id);
    }

    function doStatistics($id)
    {
        request()->merge(['competition_id' => $id, 'without_response' => true]);

        $data = app(CompetitionsStatisticsController::class)->store();
        $data2 = app(CompetitionsPredictionsStatisticsController::class)->store();

        $messages = $data['message'] . ' ' . $data2['message'];
        $results = [];

        return response(['message' => $messages, 'results' => $results]);
    }

    function tabs($id)
    {
        return response(['results' => [
            "standings" => $this->model::find($id)->seasons()->whereHas('standings')->count(),
            "teams" => $this->model::find($id)->teams()->count(),
            "past-matches" => $this->model::find($id)->games()->where('utc_date', '<=', Carbon::now())->count(),
            "upcoming-matches" => $this->model::find($id)->games()->where('utc_date', '>=', Carbon::now())->count(),
            "past-predictions" => $this->model::find($id)->games()->whereHas('prediction')->where('utc_date', '<=', Carbon::now())->count(),
            "upcoming-predictions" => $this->model::find($id)->games()->whereHas('prediction')->where('utc_date', '>=', Carbon::now())->count(),
            "odds" => Odd::whereHas('game', fn ($q) => $q->where('competition_id', $id))->count(),
            "statistics" => CompetitionStatistic::where('competition_id', $id)->count() + CompetitionPredictionStatistic::where('competition_id', $id)->count(),
            "seasons" => $this->model::find($id)->seasons()->count(),
            "details" => 1,
            "sources" => $this->model::find($id)->gameSources()->count(),
        ]]);
    }
}
