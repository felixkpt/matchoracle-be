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

        $games = $game->where('competition_id', $id)->where('results_status', 0)->get();
        $recently_fetched_games = $game->where('competition_id', $id)->where('results_status', '>', 0)->get();

        $competition = array_merge($competition->toArray(), ['games' => $games->toArray(), 'recentlyFetchedGames' => $recently_fetched_games->toArray()]);

        return $competition;
    }

    protected function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(CompetitionsController::class)->store($request);
    }


    function show($id)
    {
        return $this->competitionRepositoryInterface->show($id);
    }

    function standings($id, $season_id = null)
    {
        return $this->competitionRepositoryInterface->standings($id, $season_id);
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
        $data = $this->competitionValidationInterface->fetchMatches($id);

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

    function odds($id)
    {
        return $this->competitionRepositoryInterface->odds($id);
    }
    
    function statistics($id)
    {
        return $this->competitionRepositoryInterface->statistics($id);
    }
    
    function predictionStatistics($id)
    {
        return $this->competitionRepositoryInterface->predictionStatistics($id);
    }
    
    function doStatistics($id)
    {
        return $this->competitionRepositoryInterface->doStatistics($id);
    }
    function tabs($id)
    {
        return $this->competitionRepositoryInterface->tabs($id);
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
