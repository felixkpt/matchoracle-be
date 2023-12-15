<?php

namespace App\Services\Validations\Competition;

interface CompetitionValidationInterface
{
    public function store(): mixed;

    public function storeFromSource();

    public function storeFetch(): mixed;

    function fetchSeasons();

    function fetchStandings();

    function fetchMatches($id);

    function addSources();
}
