<?php

namespace App\Repositories\GamePrediction;

use App\Models\Game;
use App\Models\GamePrediction;
use App\Models\GamePredictionLog;
use App\Repositories\CommonRepoActions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GamePredictionRepository implements GamePredictionRepositoryInterface
{

    use CommonRepoActions;

    protected  $model;
    protected $sourceContext;

    function __construct(GamePrediction $model)
    {
        $this->model = $model;
    }

    function store()
    {
        $data = request()->all();


        $version = $data['version'];
        $type = $data['type'];
        $competition_id = $data['competition_id'];
        $date = Carbon::parse($data['date'])->format('Y-m-d');
        $predictions = $data['predictions'];

        foreach ($predictions as $game_pred) {


            $this->model->updateOrCreate(
                [
                    'version' => $version,
                    'type' => $type,
                    'competition_id' => $competition_id,
                    'game_id' => $game_pred['id'],
                ],
                [
                    'version' => $version,
                    'type' => $type,
                    'competition_id' => $competition_id,
                    'date' => $date,
                    ...$game_pred
                ]
            );

            $predicted_games = $this->model->where('date', $date)->count();
            $games = Game::whereDate('utc_date', $date);
            $total_games = $games->count();
            $unpredicted_games = $total_games - $predicted_games;

            GamePredictionLog::updateOrCreate(
                ['date' => $date],
                [
                    'date' => $date,
                    'total_games' => $total_games,
                    'predicted_games' => $predicted_games,
                    'unpredicted_games' => $unpredicted_games
                ]
            );
        }

        return response(['message' => "Games Predictions saved successfully."]);
    }
}
