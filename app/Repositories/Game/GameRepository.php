<?php

namespace App\Repositories\Game;

use App\Models\CompetitionPredictionLog;
use App\Models\Game;
use App\Models\GamePredictionType;
use App\Models\GameVote;
use App\Repositories\CommonRepoActions;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use App\Utilities\GamePredictionStatsUtility;
use App\Utilities\GameStatsUtility;
use App\Utilities\GameUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameRepository implements GameRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Game $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function index($id = null, $without_response = null)
    {

        // if predicting
        if (request()->prediction_type) {
            $this->setPredictorOptions();
        }

        $start = microtime(true);

        $gameUtilities = new GameUtility();

        $results_raw = $gameUtilities->applyGameFilters($id);

        if ($this->applyFiltersOnly) return $results_raw;

        $results = $gameUtilities->formatGames($results_raw);


        if (request()->is_predictor == 1) {

            $limit = request()->per_page;

            // added 25 percent to handle where no stats
            $results = $results->orderBy('utc_date', 'desc')->get($limit + $limit * .25)['data'];

            if (request()->task == 'train') {
                // if is train/test and results is less than 60% of limit then return empty
                if ($this->trainTestLimitFails($results, $limit)) {
                    return [];
                }
            }

            $arr = [];
            foreach ($results as $matchData) {
                if (count($arr) == $limit) break;

                $stats = (new GameStatsUtility())->addGameStatistics($matchData);
                if ($stats) {
                    $matchData->stats = $stats;
                    $arr[] = $matchData;
                }
            }

            if (request()->task == 'predict') {
                $this->updateCompetitionPredLogs($results, $arr);
            }

            $results = array_reverse($arr);

            // Log::info('GameRepository:: ', ['compe' => request()->competition_id, 'rest ct' => count($results), 'request' => $limit]);

            if (request()->task == 'train') {
                // if is train/test and results is less than 60% of limit then return empty
                if ($this->trainTestLimitFails($results, $limit)) {
                    return [];
                }
            }

            return $results;
        } else {

            if ($id) {
                $results = $results->first();
            } else {

                if (request()->get_prediction_stats) {

                    $stats = (new GamePredictionStatsUtility())->doStats(($results_raw)->whereHas('score')->limit(-1)->get()->toArray());
                    $results = $stats;
                } else {

                    if (request()->cursor_mode) {
                        $cursor = request()->get('cursor');
                        if (!$cursor) {
                            $cursor = new \Illuminate\Pagination\Cursor([
                                'utc_date' => now()->startOfDay()->toDateTimeString(),
                                'id' => null,
                            ], true, false);
                        }

                        $total = $results->count();
                        $results = $results->cursorPaginate(
                            request()->get('per_page', 15),
                            ['*'],
                            'cursor',
                            $cursor,
                            $total,
                        );
                    } else {
                        $results = $results->paginate(request()->per_page ?? 50);
                    }
                }
            }

            $arr = ['results' => $results];

            Log::critical('Time taken in secs to load matches::' . round((microtime(true) - $start)));

            if (request()->without_response) return $arr;
            return response($arr);
        }
    }

    private function trainTestLimitFails($results, $limit)
    {
        return count($results) < floor($limit * .6);
    }

    private function setPredictorOptions()
    {
        $prediction_type = $this->updateOrCreatePredictorOptions();
        if ($prediction_type) {
            preg_match_all('/\d+/', $prediction_type->name, $matches);
            $results = $matches[0];

            if (count($results) >= 3) {
                request()->merge([
                    'history_limit_per_match' => $results[0],
                    'current_ground_limit_per_match' => $results[1],
                    'h2h_limit_per_match' => $results[2],
                ]);
            }
        }
    }

    public function updateOrCreatePredictorOptions()
    {
        $name = request()->prediction_type;

        preg_match_all('/\d+/', $name, $matches);
        $results = $matches[0];

        $description = null;
        if (count($results) >= 3) {
            $description =
                "History limit per match " . $results[0] . ",
                Current ground limit per match " . $results[1] . ",
                H2H limit per match " . $results[2] . ",
                Train/test max limit " . $results[3] ?? 'N/A' . ".
                ";
        }

        return GamePredictionType::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'name' => $name,
                'description' => $description
            ]
        );
    }

    private function updateCompetitionPredLogs($games, $predictable_games)
    {

        $total_games = count($games);
        $predictable_games = count($predictable_games);

        $version = request()->version;
        $prediction_type = request()->prediction_type;

        $prediction_type = GamePredictionType::updateOrCreate([
            'name' => $prediction_type,
        ]);

        $competition_id = request()->competition_id;
        $carbon_date = Carbon::parse(request()->date);
        $date = $carbon_date->format('Y-m-d');

        CompetitionPredictionLog::updateOrCreate(
            [
                'version' => $version,
                'prediction_type_id' => $prediction_type->id,
                'competition_id' => $competition_id,
                'date' => $date,
            ],
            [
                'version' => $version,
                'prediction_type_id' => $prediction_type->id,
                'competition_id' => $competition_id,
                'date' => $date,

                'total_games' => $total_games,
                'total_predictable_games' => $predictable_games,
            ]
        );
    }

    public function today()
    {
        request()->merge(['today' => true]);
        return $this->index();
    }

    public function yesterday()
    {
        request()->merge(['yesterday' => true]);
        return $this->index();
    }

    public function tomorrow()
    {
        request()->merge(['tomorrow' => true]);
        return $this->index();
    }

    public function year($year)
    {
        request()->merge(['year' => $year]);
        return $this->index();
    }

    public function yearMonth($year, $month)
    {
        request()->merge(['year' => $year, 'month' => $month]);
        return $this->index();
    }

    public function yearMonthDay($year, $month, $day)
    {
        request()->merge(['year' => $year, 'month' => $month, 'day' => $day]);
        return $this->index();
    }

    public function dateRange($from_date, $to_date)
    {
        request()->merge(['from_date' => $from_date, 'to_date' => $to_date]);
        return $this->index();
    }

    public function store(Request $request, $data)
    {
        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        return $this->index($id);
    }

    public function vote($id, $data)
    {
        $user_id = auth()->id() ?? 0;
        $user_ip = request()->ip();

        $type = $data['type'];
        $vote = $data['vote'];
        GameVote::updateOrCreate(
            [
                'game_id' => $id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
            ],
            [
                'game_id' => $id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                "{$type}" => $vote
            ]
        );

        request()->merge(['without_response' => true, 'include_preds' => true]);

        return response(['type' => 'success', 'message' => 'Voted successfully', 'results' => $this->index($id, true)['results']]);
    }

    public function updateGame($id)
    {
        return $this->sourceContext->matchHandler()->fetchMatch($id);
    }
}
