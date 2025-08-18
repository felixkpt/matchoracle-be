<?php

namespace App\Repositories\Competition;

use App\Http\Controllers\Dashboard\Odds\OddsController;
use App\Http\Controllers\Dashboard\Statistics\CompetitionsPredictionsStatisticsController;
use App\Http\Controllers\Dashboard\Statistics\CompetitionsStatisticsController;
use App\Http\Controllers\Dashboard\Teams\TeamsController;
use App\Models\Competition;
use App\Models\CompetitionPredictionLog;
use App\Models\CompetitionPredictionStatistic;
use App\Models\CompetitionStatistic;
use App\Models\Game;
use App\Models\GamePrediction;
use App\Models\GameSource;
use App\Models\Odd;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use App\Repositories\Season\SeasonRepository;
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

        $competitions = $this->model::query()
            ->when(request()->status == 1, fn($q) => $q->where('status_id', activeStatusId()))
            ->with([
                'lastActions',
                'continent',
                'country',
                'currentSeason',
                'seasons' => fn($q) => $q->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
                    ->select(['id', 'competition_id', 'start_date', 'end_date', 'current_matchday', 'winner_id'])->orderby('start_date', 'desc'),
                'stages',
                'gameSources',

            ])
            ->when(request()->country_id, fn($q) => $q->where('country_id', request()->country_id))
            ->when(request()->is_odds_enabled == 1, fn($q) => $q->where('is_odds_enabled', true))
            ->when($id, fn($q) => $q->where('id', $id));

        if ($this->applyFiltersOnly)
            return $competitions;

        $uri = '/dashboard/competitions/';
        $results = SearchRepo::of($competitions, ['id', 'name', 'code', 'country.name', 'seasons.start_date', 'slug'])
            ->setModelUri($uri)
            ->addColumn('Name', fn($q) => $q->country->name . ' - ' . $q->name)
            ->addColumn('Created_by', 'getUser')
            ->addColumn('season', fn($q) => $q->currentSeason ? (Carbon::parse($q->currentSeason->start_date)->format('Y') . '/' . Carbon::parse($q->currentSeason->end_date)->format('Y')) : null)
            ->addColumn('Has_teams', fn($q) => $q->has_teams ? 'Yes' : 'No')
            ->addColumn('seasons_fetched', function ($q) {
                $item = $q->lastActions[0]?->seasons_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('standings_fetched', function ($q) {
                $item = $q->lastActions[0]?->standings_recent_results_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('p_matches_fetched', function ($q) {
                $item = $q->lastActions[0]?->matches_recent_results_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('u_matches_fetched', function ($q) {
                $item = $q->lastActions[0]?->matches_fixtures_last_fetch ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })->addColumn('Predictions_last_train', function ($q) {
                $item = $q->lastActions[0]?->predictions_last_train ?? null;
                return $item ? Carbon::parse($item)->diffForHumans() : 'N/A';
            })
            ->addColumn('Games_counts', function ($q) {
                $ct = Game::where('competition_id', $q->id)->count();
                if ($q->games_counts == 0) {
                    Competition::find($q->id)->update(['games_counts' => $ct]);
                }
                return $ct;
            })
            ->addColumn('Predictions_counts', function ($q) {
                $ct = GamePrediction::where('competition_id', $q->id)->where('prediction_type_id', current_prediction_type_id())->count();
                if ($q->predictions_counts == 0) {
                    Competition::find($q->id)->update(['predictions_counts' => $ct]);
                }
                return $ct;
            })
            ->addColumn('Odds_counts', function ($q) {
                $ct = Odd::whereHas('game', fn($qry) => $qry->where('competition_id', $q->id))->count();
                if ($q->odds_counts == 0) {
                    Competition::find($q->id)->update(['odds_counts' => $ct]);
                }
                return $ct;
            })
            ->addFillable('continent_id', ['input' => 'select'], 'continent_id')
            ->addFillable('has_standings', ['input' => 'select'], 'has_teams')
            ->addFillable('has_teams', ['input' => 'select'], 'has_teams')
            ->addFillable('gender', ['input' => 'select'], 'has_standings')
            ->orderby('id');

        $results = $single ? $results->first() : $results->paginate();

        return $single ? $results : response(['results' => $results]);
    }

    public function store(Request $request, $data)
    {
        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $this->index(true, $res->id)]);
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
        $this->model->findOrFail($id)
            ->lastActions()
            ->firstOrCreate(
                [
                    'competition_id' => $id,
                    'season_id' => request()->season_id,
                ],
                [
                    'competition_id' => $id,
                    'season_id' => request()->season_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

        return response(['results' => $this->index(true, $id)]);
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

        $uri = '/dashboard/countries/';
        $res = SearchRepo::of($gamesources, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->orderBy('name')
            ->paginate();

        return response(['results' => $res]);
    }

    public function seasons($id)
    {

        return app(SeasonRepository::class)->index();
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
        request()->merge(['competition_id' => $id]);

        return app(TeamsController::class)->index();
    }

    function odds($id)
    {
        request()->merge(['competition_id' => $id]);

        return app(OddsController::class)->index();
    }

    function resultsStatistics($id)
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

    function getDatesWithUnpredictedGames($id)
    {
        $from_date = Carbon::parse(request()->from_date)->format('Y-m-d');
        $to_date = Carbon::parse(request()->to_date)->format('Y-m-d');

        $dates = $this->model::find($id)
            ->games()->where('status_id', activeStatusId())
            ->whereDate('utc_date', '>=', $from_date)->whereDate('utc_date', '<=', $to_date)
            ->orderBy('utc_date', 'desc')->pluck('utc_date')->toArray();

        $dates = array_values(array_unique(array_map(fn($date) => Carbon::parse($date)->format('Y-m-d'), $dates)));

        $predicted_dates = CompetitionPredictionLog::query()->where('competition_id', $id)->whereIn('date', $dates)->whereRaw('total_predictable_games = predicted_games')->pluck('date')->toArray();

        $dates = array_values(array_diff($dates, $predicted_dates));

        return response(['results' => $dates]);
    }

    function tabs($id)
    {
        $season_id = request()->season_id;

        request()->merge(['without_response' => true]);

        $teams = app(TeamsController::class)->index();

        request()->merge(['competition_id' => $id]);

        return response(['results' => [
            "standings" => $this->model::find($id)->seasons()
                ->when($season_id, fn($q) => $this->seasonFilter($q, 'id'))
                ->whereHas('standings')->count(),

            "teams" => $teams['results']['total'] ?? 0,

            "past-matches" => $this->model::find($id)->games()
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->where('utc_date', '<=', Carbon::now())->count(),
            "upcoming-matches" => $this->model::find($id)->games()
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->where('utc_date', '>', Carbon::now())->count(),
            "past-predictions" => $this->model::find($id)->games()
                ->whereHas('prediction')
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->where('utc_date', '<=', Carbon::now())->count(),
            "upcoming-predictions" => $this->model::find($id)->games()
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->whereHas('prediction')->where('utc_date', '>=', Carbon::now())->count(),
            "odds" => Odd::whereHas(
                'game',
                fn($q) => $q->where('competition_id', $id)
                    ->when($season_id, fn($q) => $this->seasonFilter($q))
            )->count(),
            "statistics" => CompetitionStatistic::where('competition_id', $id)
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->count()
                +
                CompetitionPredictionStatistic::where('competition_id', $id)
                ->when($season_id, fn($q) => $this->seasonFilter($q))
                ->count(),
            "seasons" => $this->model::with(['seasons' => fn($q) => $q->when($season_id, fn($q) => $q->where('id', $season_id))])->find($id)->seasons->count(),
            "details" => 1,
            "sources" => $this->model::find($id)->gameSources()->count(),
        ]]);
    }

    private function seasonFilter($q, $col = 'season_id')
    {
        return  $q->where($col, request()->season_id);
    }
}
