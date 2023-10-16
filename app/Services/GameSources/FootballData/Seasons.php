<?php

namespace App\Services\GameSources\FootballData;

use App\Models\Season;
use App\Repositories\FootballData;

class Seasons
{
    protected $api;

    public function __construct()
    {
        $this->api = new FootballData();
    }

    function updateOrCreate($seasonData, $country, $competition, $is_current = false)
    {
        $winner = null;
        if (isset($seasonData->winner)) {
            $winner = app(Teams::class)->updateOrCreate($seasonData->winner, $country, $competition);
        }

        $season = Season::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'start_date' => $seasonData->startDate,
                'end_date' => $seasonData->endDate,
            ],
            [
                'competition_id' => $competition->id,
                'start_date' => $seasonData->startDate,
                'end_date' => $seasonData->endDate,
                'current_matchday' => $seasonData->currentMatchday,
                'winner_id' => $winner->id ?? null,
                'is_current' => $is_current
            ]
        );
        return $season;
    }
}
