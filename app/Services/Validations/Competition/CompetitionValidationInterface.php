<?php

namespace App\Services\Validations\Competition;

interface CompetitionValidationInterface
{
    public function store(): mixed;

    public function storeFromSource();

    public function storeFetch(): mixed;

    function addSources();
}
