<?php

namespace App\Services\GameSources\FootballData;

use App\Models\Season;
use App\Repositories\FootballData;

class Seasons
{
    public $api;

    public function __construct()
    {
        $this->api = new FootballData();
    }

    function updateOrCreate($seasonData, $country, $competition, $is_current = false, $played = null)
    {

        $arr = [
            'competition_id' => $competition->id,
            'start_date' => $seasonData->startDate,
            'end_date' => $seasonData->endDate,
            'is_current' => $is_current
        ];

        if (isset($seasonData->currentMatchday) && $seasonData->currentMatchday) {
            $arr['current_matchday'] = $seasonData->currentMatchday;
        }

        $winner = null;
        if (isset($seasonData->winner)) {
            $winner = app(Teams::class)->updateOrCreate($seasonData->winner, $country, $competition);
        }

        if ($winner) {
            $arr['winner_id'] = $seasonData->winner_id;
        }

        if ($played) {
            $arr['played'] = $played;
        }

        $season = Season::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'start_date' => $seasonData->startDate,
                'end_date' => $seasonData->endDate,
            ],
            $arr
        );
        return $season;
    }
}
