<?php

namespace App\Http\Controllers\Admin\Competitions\View;

use App\Http\Controllers\Admin\Competitions\CompetitionsController;
use App\Http\Controllers\Controller;
use App\Repositories\Competition\CompetitionRepositoryInterface;
use App\Repositories\TeamRepository;
use App\Services\Common;
use App\Services\Games\Games;
use App\Services\Validations\Competition\CompetitionValidationInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CompetitionController extends Controller
{

    public function __construct(
        private CompetitionRepositoryInterface $competitionRepositoryInterface,
        private CompetitionValidationInterface $competitionValidationInterface,
    ) {
    }

    function index($id)
    {
        $competition = $this->competitionRepositoryInterface->model::findById($id, ['*'], ['teams'])->toArray();
        return response('Competitions/Competition/Show', compact('competition'));
    }

    function checkMatches($id)
    {
        $competition = $this->competitionRepositoryInterface->model::findById($id);
        return response('Competitions/Competition/Actions', compact('competition'));
    }

    function checkMatchesAction($id)
    {
        return $this->get_method(request()->status, $id);
    }

    function get_method($action, $id)
    {
        $object = $this;
        return call_user_func_array(array($object, $action), ['id' => $id]);
    }

    protected function predictions($id)
    {
        $competition = $this->competitionRepositoryInterface->model::findById($id);
        return response('Competitions/Competition/Predictions', compact('competition'));
    }

    protected function fixtures($id)
    {
        $competition = $this->getCompetition($id);

        return response('Competitions/Competition/Fixtures', compact('competition'));
    }

    protected function getFixtures($id)
    {

        $testdate = Carbon::now()->subDays(2)->toDateTimeString();

        $chunk = 3;
        if (isset(request()->limit) && request()->limit < $chunk) $chunk = request()->limit;

        $all_res = [];
        $repo = new TeamRepository();
        $repo->model
            // ->where('id', '01h40p8jm45fzpkwwvk121hnzr')
            ->where('competition_id', $id)->where(function ($q) use ($testdate) {
                $q->where('last_fetch', '<=', $testdate)->orWhereNull('last_fetch');
            })
            ->orderby('last_fetch', 'asc')
            ->chunk($chunk, function ($teams) use (&$all_res) {

                // Stop chunk processing of limit is supplied and reached
                if (request()->limit && count($all_res) >= request()->limit) return false;

                $res = [];
                foreach ($teams as $team) {
                    $games = new Games();
                    $res[] = ['team' => $team->name . ' (#' . $team->id . ')', 'fetch_details' => $games->fixtures($team->id, false)];
                }

                $all_res = array_merge($all_res, $res);
            });

        $competition = $this->getCompetition($id);

        return response(['results' => ['res' => $all_res, 'competition' => $competition]]);
    }

    function getCompetition($id)
    {
        $testdate = Carbon::now()->subDays(1)->toDateTimeString();

        $competition = $this->competitionRepositoryInterface->model::findById($id, ['*'], ['teams' => function ($q) use ($testdate) {
            $q->where('last_fetch', '<=', $testdate)->orWhereNull('last_fetch');
        }]);

        $recently_fetched_teams = $this->competitionRepositoryInterface->model::findById($id, ['*'], ['teams' => function ($q) use ($testdate) {
            $q->where('last_fetch', '>', $testdate)->orderby('last_fetch', 'desc');
        }])->teams;

        $competition = array_merge($competition->toArray(), ['recentlyFetchedTeams' => $recently_fetched_teams->toArray()]);
        return $competition;
    }

    protected function detailedFixtures($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = autoModel($table);

        Common::checkCompetitionAbbreviation($table);

        $competition = $this->getGames($id);

        return response('Competitions/Competition/DetailedFixtures', compact('competition'));
    }

    protected function getDetailedFixtures($id)
    {

        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = autoModel($table);

        $games = new Games();
        $all_res = $games->detailedFixture($id, $game, true);

        $this->competitionRepositoryInterface->model::update($id, ['last_detailed_fetch' => Carbon::now()]);

        $competition = $this->getGames($id);
        return response(['results' => ['res' => $all_res, 'competition' => $competition]]);
    }

    function getGames($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = autoModel($table);

        $competition = $this->competitionRepositoryInterface->model::findById($id, ['*']);

        $games = $game->where('competition_id', $id)->where('update_status', 0)->get();
        $recently_fetched_games = $game->where('competition_id', $id)->where('update_status', '>', 0)->get();

        $competition = array_merge($competition->toArray(), ['games' => $games->toArray(), 'recentlyFetchedGames' => $recently_fetched_games->toArray()]);

        return $competition;
    }

    protected function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(CompetitionsController::class)->store($request);
    }

    protected function results($id)
    {
        dd($id, 'ress');
    }

    function show($id)
    {
        return $this->competitionRepositoryInterface->show($id);
    }

    function standings($id, $season_id = null)
    {
        return $this->competitionRepositoryInterface->standings($id, $season_id);
    }

    function matches($id, $season = null)
    {
        return $this->competitionRepositoryInterface->matches($id, $season);
    }

    function addSources(Request $request, $id)
    {
        $request->merge(['id' => $id]);

        $data = $this->competitionValidationInterface->addSources();

        return $this->competitionRepositoryInterface->addSources($request, $data);
    }

    function fetchSeasons($id)
    {
        $data = $this->competitionValidationInterface->fetchSeasons();

        return $this->competitionRepositoryInterface->fetchSeasons($id, $data);
    }

    function fetchStandings($id)
    {
        $data = $this->competitionValidationInterface->fetchStandings();

        return $this->competitionRepositoryInterface->fetchStandings($id, $data);
    }

    function fetchMatches($id)
    {
        $data = $this->competitionValidationInterface->fetchMatches();

        return $this->competitionRepositoryInterface->fetchMatches($id, $data);
    }

    function seasons($id)
    {
        return $this->competitionRepositoryInterface->seasons($id);
    }

    function teams($id)
    {
        return $this->competitionRepositoryInterface->teams($id);
    }

    function updateStatus($id)
    {
        return $this->competitionRepositoryInterface->updateStatus($id);
    }

    function destroy($id)
    {
        return $this->competitionRepositoryInterface->destroy($id);
    }
}
