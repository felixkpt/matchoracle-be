<?php

namespace App\Repositories\Team;

use App\Models\CoachContract;
use App\Models\Country;
use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo;
use App\Services\Client;
use App\Services\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class TeamRepository implements TeamRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Team $model)
    {
    }

    public function index($id = null)
    {

        $teams = $this->model::with(['country', 'competition', 'address', 'venue', 'coachContract' => fn ($q) => $q->with('coach'), 'gameSources'])
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/teams/';
        $results = SearchRepo::of($teams, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionItem(
                [
                    'title' => 'Add Sources',
                    'action' => ['title' => 'add-sources', 'modal' => 'add-sources', 'native' => null, 'use' => 'modal']
                ],
                'Update Status'
            )
            ->addActionItem(
                [
                    'title' => 'Update Coach',
                    'action' => ['title' => 'update-coach', 'modal' => 'update-coach', 'native' => null, 'use' => 'modal']
                ],
                'Update status'
            )
            ->addActionColumn('action', $uri, 'native')
            ->addFillable('website', 'website', ['input' => 'input', 'type' => 'url'])
            ->addFillable('tla', 'tla', ['input' => 'input', 'type' => 'text', 'capitalize' => true])
            ->addFillable('founded', 'founded', ['input' => 'input', 'type' => 'number'])
            ->htmls(['Status', 'Crest'])
            ->removeFillable(['coach_id'])
            ->orderby('name');

        $results = $id ? $results->first() : $results->paginate(25);

        return response(['results' => $results]);
    }

    function matches($id = null)
    {
        $all_params = request()->all_params;

        if (is_array($all_params)) {
            $team_id = $all_params['team_id'] ?? null;
            $team_ids = $all_params['team_ids'] ?? null;
            $playing = $all_params['playing'] ?? null;
            $to_date = $all_params['to_date'] ?? Carbon::now()->format('Y-m-d');
            $currentground = $all_params['currentground'] ?? null;
            $season_id = $all_params['season_id'] ?? null;
            $type = $all_params['type'] ?? null;
            $order_by = $all_params['order_by'] ?? null;
            $order_direction = $all_params['order_direction'] ?? 'desc';
            $per_page = $all_params['per_page'] ?? null;
            $without_response = $all_params['without_response'] ?? null;
        } else {
            $team_id = request()->team_id ?? null;
            $team_ids = request()->team_ids ?? null;
            $playing = request()->playing ?? null;
            $to_date = request()->to_date ?? Carbon::now()->format('Y-m-d');
            $currentground = request()->currentground ?? null;
            $season_id = request()->season_id ?? null;
            $type = request()->type ?? null;
            $order_by = request()->order_by ?? null;
            $order_direction = request()->order_direction ?? 'desc';
            $per_page = request()->per_page ?? null;
            $without_response = request()->without_response ?? null;
        }

        if ($id) $team_id = $id;

        request()->merge(['order_by' => $order_by, 'order_direction' => $order_direction]);

        $homeWinVotes = function ($q) {
            return $q->votes->where('winner', 'home')->count();
        };

        $drawVotes = function ($q) {
            return $q->votes->where('winner', 'draw')->count();
        };

        $awayWinVotes = function ($q) {
            return $q->votes->where('winner', 'away')->count();
        };

        $hasCurrentUserWinnerVote = function ($q) {
            return !!$q->votes->where(function ($q) {
                return $q->where('user_id', auth()->id())->orWhere('user_ip', request()->ip());
            })->whereNotNull('winner')->first();
        };

        $teamsMatch = function ($q) use ($team_ids, $playing) {

            return $q->where(function ($q) use ($team_ids, $playing) {
                [$home_team_id, $away_team_id] = $team_ids;

                $arr = [['home_team_id', $home_team_id], ['away_team_id', $away_team_id]];
                $arr2 = [['home_team_id', $away_team_id], ['away_team_id', $home_team_id]];

                if ($playing == 'home-away') {
                    $q->where($arr);
                } else {
                    $q->where($arr)->orWhere($arr2);
                }
            });
        };

        // if ($currentground)
        //     Log::info('currentground', [$currentground, $to_date]);

        $games = Game::with(['competition' => fn ($q) => $q->with(['country', 'currentSeason']), 'home_team', 'away_team', 'score', 'votes', 'referees'])
            ->when($team_id, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', $team_id)->orWhere('away_team_id', $team_id)))
            ->when($team_ids, $teamsMatch)
            ->when($currentground, fn ($q) => $currentground == 'home' ? $q->where('home_team_id', $team_id) : ($currentground == 'away' ? $q->where('away_team_id', $team_id) :  $q))
            ->when($season_id, fn ($q) => $q->where('season_id', $season_id))

            ->when($type, fn ($q) => $type == 'played' ? $q->where('utc_date', '<', $to_date) : ($type == 'upcoming' ? $q->where('utc_date', '>=', Carbon::now()) :  $q))
            ->when($to_date, fn ($q) => $q->whereDate('utc_date', '<=', Carbon::parse($to_date)->format('Y-m-d')));

        $uri = '/admin/matches/';
        $results = SearchRepo::of($games, ['id'])
            ->addColumn('is_future', fn ($q) => Carbon::parse($q->utc_date)->isFuture())
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('home_win_votes', $homeWinVotes)
            ->addColumn('draw_votes', $drawVotes)
            ->addColumn('away_win_votes', $awayWinVotes)
            ->addColumn('current_user_winner_vote', $hasCurrentUserWinnerVote)
            ->addColumnWhen(true, 'home_team_league_details', fn ($q) => $this->teamLeagueDetails($q->home_team_id, $q->id))
            ->addColumnWhen(true, 'away_team_league_details', fn ($q) => $this->teamLeagueDetails($q->away_team_id, $q->id))
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status']);

        $results = $results->paginate($per_page);

        if ($without_response) {
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

        $uri = '/admin/teams/';
        $res = SearchRepo::of($teams, ['name', 'founded'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->logo ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->orderby('priority_number')
            ->addActionColumn('action', $uri, 'native')
            ->paginate();
        return response(['results' => $res]);
    }
}
