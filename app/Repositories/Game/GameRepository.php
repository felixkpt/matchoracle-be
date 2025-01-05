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
        if (request()->prediction_type) {
            $this->setPredictorOptions();
        }

        $start = microtime(true);

        $gameUtilities = new GameUtility();
        $results_raw = $gameUtilities->applyGameFilters($id);

        if ($this->applyFiltersOnly) {
            return $results_raw;
        }

        $results = $gameUtilities->formatGames($results_raw);

        if (request()->is_predictor) {
            return $this->getPredictorGames($results);
        }

        return $this->getStandardGames($id, $results, $results_raw, $start, $without_response);
    }

    /**
     * Handles logic when the request is a predictor.
     */
    private function getPredictorGames($results)
    {
        $compe_id = request()->competition_id;
        $start = now();
        Log::info("GetPredictorGames: START, Competition: #$compe_id");

        $limit = request()->per_page;
        $results = $this->getPredictorResults($results, $limit);

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

        $timetaken = $start->diffInMinutes(now()) . ' mins';
        Log::info("GetPredictorGames: END, Competition:: #$compe_id, (took $timetaken)");

        if ($this->shouldAbortTrainTask($results, $limit)) {
            return [];
        }

        return $results;
    }

    /**
     * Retrieves and filters predictor results.
     */
    private function getPredictorResults($results, $limit)
    {
        $results = $results->orderBy('utc_date', 'desc')->get($limit + $limit * .25)['data'];

        if (request()->task == 'train' && $this->trainTestLimitFails($results, $limit)) {
            return [];
        }

        return $results;
    }

    /**
     * Determines if the train task should abort due to limit failure.
     */
    private function shouldAbortTrainTask($results, $limit)
    {
        return request()->task == 'train' && $this->trainTestLimitFails($results, $limit);
    }

    /**
     * Handles logic for standard tasks (non-predictor requests).
     */
    private function getStandardGames($id, $results, $results_raw, $start, $without_response)
    {
        if ($id) {
            $results = $results->first();
        } else {
            $results = $this->handleStandardResults($results, $results_raw);
        }

        $arr = ['results' => $results];

        Log::critical('Time taken in secs to load matches::' . round((microtime(true) - $start)));

        if ($without_response) {
            return $arr;
        }

        return response($arr);
    }

    /**
     * Processes standard results including pagination and grouping.
     */
    private function handleStandardResults($results, $results_raw)
    {
        if (request()->get_prediction_stats) {
            $stats = (new GamePredictionStatsUtility())->doStats(
                ($results_raw)->whereHas('score')->limit(-1)->get()->toArray()
            );
            return $stats;
        }

        $results = $results->paginate(request()->per_page ?? 50);

        if (request()->group_by === 'competition') {
            $data = $results['data'];
            $groupedData = collect($data)->groupBy('competition_id');
            $results['data'] = $groupedData;
        }

        return $results;
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
