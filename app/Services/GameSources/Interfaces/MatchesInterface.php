<?php

namespace App\Services\GameSources\Interfaces;

interface MatchesInterface
{
    function fetchMatches($id, $matchday);
}
