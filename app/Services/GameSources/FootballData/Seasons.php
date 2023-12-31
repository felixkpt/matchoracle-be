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
        $winner = null;
        if (isset($seasonData->winner)) {
            $winner = app(Teams::class)->updateOrCreate($seasonData->winner, $country, $competition);
        }

        $arr = [
            'competition_id' => $competition->id,
            'start_date' => $seasonData->startDate,
            'end_date' => $seasonData->endDate,
            'current_matchday' => $seasonData->currentMatchday,
            'winner_id' => $winner->id ?? null,
            'is_current' => $is_current
        ];

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
