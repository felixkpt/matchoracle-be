<?php

namespace App\Services\GameSources\Interfaces;

interface CompetitionsHandlerInterface
{
    function updateOrCreate($data);

    function fetchSeasons($competition_id, $season_id = null);

    function fetchStandings($competition_id, $season_id = null);

    function fetchMatches($competition_id, $matchday);
}
