<?php

namespace App\Services\GameSources\Interfaces;

interface CompetitionsInterface
{
    function updateOrCreate($data);

    function fetchSeasons($id, $season = null);

    function fetchStandings($id, $season = null);

    function fetchMatches($id, $matchday);
}
