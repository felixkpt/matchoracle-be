<?php

namespace App\Services\Validations\Team;

interface TeamValidationInterface
{
    public function store(): mixed;

    public function storeFetch(): mixed;

    public function storeFromSource();
    
    function addSources();

    function updateCoach();
}
