<?php

namespace App\Services\GameSources\Interfaces;

interface CompetitionsInterface
{
    function updateOrCreate($data);

    function fetchStandings($id);

    function fetchMatches($id, $match_day);
}
