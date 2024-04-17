<?php

namespace App\Http\Controllers\Admin\Teams\View;

use App\Http\Controllers\Admin\Predictions\PredictionsController;
use App\Http\Controllers\Admin\Teams\TeamsController;
use App\Http\Controllers\CommonControllerMethods;
use App\Http\Controllers\Controller;
use App\Repositories\SearchRepo;
use App\Repositories\Team\TeamRepositoryInterface;
use App\Services\Common;
use App\Services\Games\Games;
use App\Services\Validations\Team\TeamValidationInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    use CommonControllerMethods;

    function __construct(
        private TeamRepositoryInterface $teamRepositoryInterface,
        private TeamValidationInterface $teamValidationInterface,
    ) {
        $this->repo = $teamRepositoryInterface;
    }

    function show($id)
    {
        return $this->teamRepositoryInterface->show($id);
    }

    protected function update(Request $request, $id)
    {
        $request->merge(['id' => $id]);
        return app(TeamsController::class)->store($request);
    }

    function addSources(Request $request, $id)
    {
        $request->merge(['id' => $id]);

        $data = $this->teamValidationInterface->addSources();

        return $this->teamRepositoryInterface->addSources($request, $data);
    }

    function updateCoach(Request $request, $id)
    {
        $request->merge(['id' => $id]);

        $data = $this->teamValidationInterface->updateCoach();

        return $this->teamRepositoryInterface->updateCoach($request, $data);
    }

    function matches($id)
    {
        return $this->teamRepositoryInterface->matches($id);
    }

    function head2head($id)
    {
        return $this->teamRepositoryInterface->head2head($id);
    }

    function predictions($id)
    {
        request()->merge(['team_id' => $id]);
        return app(PredictionsController::class)->index();
    }

    function getGames($id)
    {

        $builder = DB::table(DB::query()->fromSub($this->query($id, 0), 'subquery'));

        $max_past_years = 4;
        $i = 0;
        while ($builder->count() < 400 && $i < $max_past_years) {
            $i++;

            $games = $this->query($id, $i);
            if ($games === false)
                break;

            $builder->when($games, function ($q) use ($games) {
                return $q->unionAll(DB::query()->fromSub($games, 'subquery'));
            });
        }

        $searchable = ['home_team', 'away_team'];
        $sortable = ['id', 'date_time', 'home_team', 'away_team', 'ht_results', 'ft_results',];
        $fillable = ['date_time', 'home_team', 'away_team', 'ht_results', 'ft_results'];

        $games = SearchRepo::of(
            DB::query()->fromSub($builder, 'builder'),
            $searchable,
            $sortable,
            $fillable
        )
            ->addColumn(
                'detailed',
                function ($q) {
                    return $q->home_team . ' vs ' . $q->away_team;
                }
            )->paginate(1, ['*']);

        return response(['results' => $games]);
    }

    function query($id, $i = 0)
    {
        $y = Carbon::now()->subYears($i)->year . '_games';
        try {
            $autoModel = autoModel($y);
        } catch (Exception $e) {
            return false;
        }

        $table = $autoModel->getTable();

        $games = DB::table($table)
            ->join('teams as hteam', $table . '.home_team_id', 'hteam.id',)
            ->join('teams as ateam', $table . '.away_team_id', 'ateam.id',)
            ->where(function ($q) use ($table, $id) {
                $q->where($table . '.home_team_id', $id)->orWhere($table . '.away_team_id', $id);
            })
            ->select(
                $table . '.*',
                'hteam.name as home_team',
                'ateam.name as away_team'
            );

        return $games;
    }

    function checkMatches($id)
    {
        $team = $this->repo->findById($id);
        return response('Teams/Team/Actions', compact('team'));
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

    protected function fixtures($id)
    {
        $team = $this->repo->findById($id);

        $games = new Games();
        $games = $games->getGames($id, true);
        $team = array_merge($team->toArray(), $games);

        return response('Teams/Team/Fixtures', compact('team'));
    }

    protected function getFixtures($id)
    {
        $games = new Games();

        return response(['results' => $games->fixtures($id)]);
    }

    // protected function detailedFixtures($id)
    // {
    //     // Detailed fixture for existing games, so let's get this year's table
    //     $table = Carbon::now()->year . '_games';

    //     $game = autoModel($table);

    //     $all_res = [];
    //     $game
    //         ->where(fn ($q) => $q->where('home_team_id', $id)->orwhere('away_team_id', $id))
    //         ->where('fetching_fixture_state', 0)->chunk(2, function ($games) use (&$all_res, $table) {
    //             $res = [];
    //             foreach ($games as $game) {
    //                 $gamest = new Games();
    //                 $game = $game->toArray();
    //                 $game['table'] = $table;
    //                 $res[] = ['fixture' => '(#' . $game['id'] . ')', 'fetch_details' => ['action' => $gamest->detailedFixture($game)]];
    //             }

    //             $all_res = array_merge($all_res, $res);

    //             return false;
    //         });

    //     $games = $this->getGames($id);
    //     return response(['results' => ['res' => $all_res, 'games' => $games]]);
    // }

    protected function detailedFixtures($id)
    {
        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $game = autoModel($table);

        Common::checkCompetitionAbbreviation($table);

        $games = new Games();

        $team = $this->repo->findById($id);
        $games = $games->getGames($id, true);

        $team = array_merge($team->toArray(), $games);
        return response('Teams/Team/DetailedFixtures', compact('team'));
    }

    protected function getDetailedFixtures($id)
    {

        // Detailed fixture for existing games, so let's get this year's table
        $table = Carbon::now()->year . '_games';

        $autoModel = autoModel($table);

        $games = new Games();
        $all_res = $games->detailedFixture($id, $autoModel, false);

        $this->repo->update($id, ['last_detailed_fetch' => Carbon::now()]);

        $team = $this->repo->findById($id);
        $games = $games->getGames($id, true);

        $team = array_merge($team->toArray(), $games);

        return response(['results' => ['res' => $all_res, 'team' => $team]]);
    }

    protected function results($id)
    {
        dd($id, 'ress');
    }
}
