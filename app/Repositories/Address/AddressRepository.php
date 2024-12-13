<?php

namespace App\Repositories\Address;

use App\Models\Address;
use App\Repositories\CommonRepoActions;
use App\Repositories\SearchRepo\SearchRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AddressRepository implements AddressRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected Address $model)
    {
    }

    public function index()
    {

        $teams = $this->model::query()
            ->when(request()->status == 1, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id));

        if ($this->applyFiltersOnly) return $teams;

        $uri = '/teams/addresses';
        $statuses = SearchRepo::of($teams, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->orderby('name')
            ->paginate(request()->competition_id ? $teams->count() : 20);

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
        // $countries = $this->model::with(['continent', 'country', 'gameSources'])->where('id', $id);
        $team = $this->model::with(['country', 'gameSources'])->where('id', $id);

        $uri = '/teams/address';
        $statuses = SearchRepo::of($team, ['id', 'name'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->first();

        return response(['results' => $statuses]);
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

        $teams = Address::whereIn('id', $arr);

        $uri = '/teams/';
        $res = SearchRepo::of($teams, ['name', 'founded'])
            ->setModelUri($uri)
            ->addColumn('Created_by', 'getUser')
            ->orderby('priority_number')
            ->paginate();
        return response(['results' => $res]);
    }
}
