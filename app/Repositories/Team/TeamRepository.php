<?php

namespace App\Repositories\Team;

use App\Models\CoachContract;
use App\Models\Game;
use App\Models\Team;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use App\Utilities\GameUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class TeamRepository implements TeamRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Team $model)
    {
    }

    public function index($id = null)
    {

        Log::info("(message)", [request()->season_id]);

        $teams = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->with(['country', 'competition', 'address', 'venue', 'coachContract' => fn ($q) => $q->with('coach'), 'gameSources'])
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->season_id, function ($q) {
                // Join with the pivot table and filter by season_id
                $q->whereHas('seasons', function ($query) {
                    $query->where('season_teams.season_id', request()->season_id);
                });

                // Join season_teams to access pivot columns for ordering
                $q->join('season_teams', 'teams.id', '=', 'season_teams.team_id')
                    ->where('season_teams.season_id', request()->season_id);
            })
            ->when($id, fn ($q) => $q->where('id', $id));


        if ($this->applyFiltersOnly) return $teams;

        $uri = '/dashboard/teams/';
        $results = SearchRepo::of($teams, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->addFillable('website', ['input' => 'input', 'type' => 'url'], 'website')
            ->addFillable('tla', ['input' => 'input', 'type' => 'text', 'capitalize' => true], 'tla')
            ->addFillable('founded', ['input' => 'input', 'type' => 'number'], 'founded')
            ->removeFillable(['coach_id']);

        if (request()->season_id) {
            $results->orderBy('season_teams.position');
        } else {
            $results->orderby('name');
        }

        $results = $id ? $results->first() : $results->paginate(25);

        $arr = ['results' => $results];

        if (request()->without_response) return $arr;
        return response($arr);
    }

    function matches($team_id = null)
    {

        request()->merge(['team_id' => $team_id]);
        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters();

        if (request()->type == 'past' && request()->with_upcoming) {
            request()->merge(['type' => 'upcoming', 'limit' => request()->upcoming_limit ?? 3, 'to_date' => null]);

            if (request()->per_page) {
                request()->merge(['per_page' => request()->per_page + request()->upcoming_limit ?? 3]);
            }

            $upcoming_results = $gameUtilities->applyGameFilters();
            $results = $upcoming_results->union($results);
        }

        $results = $gameUtilities->formatGames($results);

        $results = $results->paginate();

        if (request()->without_response || (isset(request()->all_params['without_response']))) {
            request()->merge(['all_params' => null]);
            return $results;
        }

        return response(['results' => $results]);
    }

    function teamLeagueDetails($id, $game_id = null)
    {
        $arr = [
            'position' => 0,
            'played_games' => 0,
            'won' => 0,
            'lost' => 0,
            'points' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
        ];

        $team = $this->model->find($id);
        if ($game_id) {
            $target_season = Game::find($game_id)->season;
        } else {
            $target_season = $team->competition->currentSeason;
        }

        if ($target_season) {

            $standings = $target_season->standings;
            // Log::info('Target_season', ['ID' => $target_season->id, 'competiton' => $target_season->competition_id, 'start:' => $target_season->start_date, 'end_date' => $target_season->end_date, 'current_matchday' => $target_season->current_matchday, 'Standings' => $standings]);

            foreach ($standings as $standing) {
                $lp_details = $standing->standingTable->where('team_id', $team->id)->first();
                if ($lp_details) {

                    $arr = [
                        'position' => $lp_details->position,
                        'played_games' => $lp_details->played_games,
                        'won' => $lp_details->won,
                        'lost' => $lp_details->lost,
                        'points' => $lp_details->points,
                        'goals_for' => $lp_details->goals_for,
                        'goals_against' => $lp_details->goals_against,
                        'goal_difference' => $lp_details->goal_difference,
                    ];

                    // Log::info("TeamLeaguePosition::", [$arr]);
                    break;
                } else {
                    Log::info('TeamLeaguePosition', ['No position details.']);
                }
            }
        }

        return $arr;
    }

    public function head2head($id)
    {
        $game = Game::find($id);

        $team_ids = [$game->home_team_id, $game->away_team_id];

        $all_params = [
            'team_id' => null,
            'team_ids' => null,
            'playing' => request()->playing,
            'to_date' => request()->to_date,
            'currentground' => null,
            'season_id' => null,
            'type' => request()->type,
            'order_by' => 'utc_date',
            'order_direction' => request()->type == 'upcoming' ? 'asc' : 'desc',
            'per_page' => request()->per_page,
            'without_response' => null,
        ];

        $all_params = request()->all_params ?? $all_params;

        request()->merge(['all_params' => array_merge($all_params, ['team_ids' => $team_ids])]);

        $matches = $this->matches();

        return $matches;
    }


    public function store(Request $request, $data)
    {
        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Team ' . $action . ' successfully', 'results' => $res]);
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

    public function show($id)
    {
        return $this->index($id);
    }

    function addSources(Request $request, $data)
    {
        $team = $this->model::find($request->id);

        foreach ($data as $key => $item) {

            $subscription_expires = $item['subscription_expires'];
            $is_subscribed = $subscription_expires === 'never';
            if (!$is_subscribed && $subscription_expires) {
                $subscription_expires = Carbon::parse($subscription_expires)->format('Y-m-d H:i:s');
                $is_subscribed = Carbon::parse($subscription_expires)->isFuture();
            }

            $arr = ['uri' => $item['uri'], 'source_id' => $item['source_id'], 'game_source_team.subscription_expires' => $subscription_expires, 'game_source_team.is_subscribed' => $is_subscribed];

            // Check if $item (URI && source_id) is not null before proceeding
            if ($item['uri'] || $item['source_id']) {
                // Check if the game source with the given ID doesn't exist
                if (!$team->gameSources()->where('game_source_id', $key)->exists()) {
                    // Attach the relationship with the URI & or source_id
                    $team->gameSources()->attach($key, $arr);
                } else {
                    $team->gameSources()->where('game_source_id', $key)->update($arr);
                }
            } else {
                // Detach the relationship if URI & source_id are null
                $team->gameSources()->detach($key);
            }

            return response(['type' => 'success', 'message' => "Sources for {$team->name} updated successfully"]);
        }
    }

    function updateCoach(Request $request, $data)
    {
        $team = $this->model::find($request->id);

        $arr = ['team_id' => $request->id];
        CoachContract::updateOrCreate(
            $arr,
            array_merge($arr, $data)
        );

        return response(['type' => 'success', 'message' => "Coach for {$team->name} updated successfully"]);
    }


    public function seasons($id)
    {
        $team = $this->model::with(['seasons'])->findOrFail($id);

        return response(['results' => $team->seasons]);
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

        $team = $query->findOrFail($id);

        return response(['results' => $team]);
    }

    function teams($id)
    {

        $team = $this->model::with(['teams'])->find($id);

        $arr = isset($team->teams) ? $team->teams->pluck('id') : [];

        $teams = Team::whereIn('id', $arr);

        $uri = '/dashboard/teams/';
        $res = SearchRepo::of($teams, ['name', 'founded'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->orderby('priority_number')
            ->paginate();

        return response(['results' => $res]);
    }
}
