<?php

namespace App\Repositories\Game;

use App\Models\Game;
use App\Models\GamePredictionType;
use App\Models\GameVote;
use App\Repositories\CommonRepoActions;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use App\Utilities\GamePredictionStatsUtility;
use App\Utilities\GameStatsUtility;
use App\Utilities\GameUtility;
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

        $gameUtilities = new GameUtility();

        $results_raw = $gameUtilities->applyGameFilters($id);

        if ($this->applyFiltersOnly) return $results_raw;

        $results = $gameUtilities->formatGames($results_raw);

        if (request()->is_predictor == 1) {

            $limit = request()->per_page;

            // added 25 percent to handle where no stats
            $results = $results->orderBy('utc_date', 'desc')->get($limit + $limit * .25)['data'];

            $arr = [];
            foreach ($results as $matchData) {
                if (count($arr) == $limit) break;

                $stats = (new GameStatsUtility())->addGameStatistics($matchData);
                if ($stats) {
                    $matchData->stats = $stats;
                    $arr[] = $matchData;
                }
            }

            $results = array_reverse($arr);

            Log::info('GameRepository:: ', ['compe' => request()->competition_id, 'rest ct' => count($results), 'request' => $limit]);

            return request()->task == 'train' && count($results) < 100 ? [] : $results;
        } else {

            if ($id) {
                $results = $results->first();
            } else {

                if (request()->get_prediction_stats) {

                    $stats = (new GamePredictionStatsUtility())->doStats(($results_raw)->whereHas('score')->limit(-1)->get()->toArray());
                    $results = $stats;
                } else {
                    $results = $results->paginate(request()->per_page ?? 50);
                }
            }

            $arr = ['results' => $results];

            if (request()->without_response) return $arr;
            return response($arr);
        }
    }

    private function setPredictorOptions()
    {
        $prediction_type = $this->updateOrCreatePredictorOptions();
        if ($prediction_type) {
            preg_match_all('/\d+/', $prediction_type->name, $matches);
            $results = $matches[0];

            if (count($results) == 3) {
                request()->merge([
                    'history_limit_per_match' => $results[0],
                    'current_ground_limit_per_match' => $results[1],
                    'h2h_limit_per_match' => $results[2],
                ]);
            }
        }
    }

    private function updateOrCreatePredictorOptions()
    {
        $name = request()->prediction_type;
        return GamePredictionType::updateOrCreate(
            [
                'name' => $name,
            ],
            [
                'name' => $name,
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

        request()->merge(['without_response' => true, 'break_preds' => true]);

        return response(['type' => 'success', 'message' => 'Voted successfully', 'results' => $this->index($id, true)['results']]);
    }

    public function updateGame($id)
    {
        return $this->sourceContext->matchHandler()->fetchMatch($id);
    }
}
