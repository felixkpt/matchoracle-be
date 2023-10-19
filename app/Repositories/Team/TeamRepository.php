<?php

namespace App\Repositories\Team;

use App\Models\CoachContract;
use App\Models\Country;
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
        sleep(1);

        $teams = $this->model::with(['country', 'competition', 'address', 'venue', 'coachContract' => fn($q) => $q->with('coach'), 'gameSources'])
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/teams/';
        $results = SearchRepo::of($teams, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->crest ?? asset('storage/football/defaultflag.png')) . '" />')
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

        $results = $id ? $results->first() : $results->paginate();

        return response(['results' => $results]);
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
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->crest ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->orderby('priority_number')
            ->addActionColumn('action', $uri, 'native')
            ->paginate();
        return response(['results' => $res]);
    }
}
