<?php

namespace App\Repositories\Team;

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

    public function index()
    {
        sleep(1);

        $teams = $this->model::with(['country'])
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id));

        // Log::alert('cc', [$teams->count()]);

        $uri = '/admin/teams/';
        $statuses = SearchRepo::of($teams, ['id', 'name'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->crest ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
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

    function storeFetch(Request $request)
    {

        $source = request()->source;
        $has_teams = request()->has_teams;

        if ($has_teams) {
            $source = rtrim($source, '/');
            if (!Str::endsWith($source, '/standing'))
                $source .= '/standing';
        }

        $source = parse_url($source);

        $source = $source['path'];

        $suff = '/standing';
        if (Str::endsWith($source, $suff)) {
            $source = Str::beforeLast($source, $suff);
        } else {
            if (Client::status(Common::resolve($source . $suff)) === 200)
                $has_teams = true;
        }

        $exists = Team::where('url', $source)->first();
        if ($exists)
            return response(['results' => ['message' => 'Whoops! It seems the team is already saved (#' . $exists->id . ').']]);

        if (!Country::count())
            return response(['results' => ['message' => 'Countries list is empty.']]);

        $country = Common::saveCountry($source);

        $html = Client::request(Common::resolve($source));

        if ($html === null) return;

        $crawler = new Crawler($html);

        $team = $crawler->filter('h1.frontH')->each(fn (Crawler $node) => ['src' => $source, 'name' => $node->text(), 'img' => $node->filter('img')->attr('src')]);
        $team = $team[0] ?? null;

        $team = Common::saveTeam($team, null, $has_teams);

        if ($team)
            return Common::updateTeamAndHandleTeams($team, $country, $has_teams, null, false);
        else
            return response(['results' => ['message' => 'Cannot get team.']]);
    }

    public function show($id)
    {
        // $countries = $this->model::with(['continent', 'country', 'gameSources'])->where('id', $id);
        $team = $this->model::with(['country', 'gameSources'])->where('id', $id);

        $uri = '/admin/teams/';
        $statuses = SearchRepo::of($team, ['id', 'name', 'country.name', 'slug'])
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addColumn('has_teams', fn ($q) => $q->has_teams ? 'Yes' : 'No')
            ->addActionItem(
                [
                    'title' => 'Add Sources',
                    'action' => ['title' => 'add-sources', 'modal' => 'add-sources', 'native' => null, 'use' => 'modal']
                ],
                'Status update'
            )
            ->addColumn('Crest', fn ($q) => '<img class="symbol-image-sm bg-body-secondary border" src="' . ($q->crest ?? asset('storage/football/defaultflag.png')) . '" />')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'Crest'])
            ->addFillable('continent_id', 'continent_id', ['input' => 'select'])
            ->addFillable('has_teams', 'has_teams', ['input' => 'select'])
            ->orderby('priority_number')
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
