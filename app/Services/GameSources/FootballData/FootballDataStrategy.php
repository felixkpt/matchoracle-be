<?php

namespace App\Services\GameSources;

use App\Services\GameSources\FootballData\FootballDataInit;

class FootballDataStrategy implements GameSourceStrategyInterface
{
    protected $footballData;

    public function __construct()
    {
        $this->footballData = new FootballDataInit();
    }

    public function getId()
    {
        return $this->footballData->api->sourceId;
    }

    public function initialCompetitions()
    {
        return $this->footballData->initialCompetitions();
        
    }

    public function competitions()
    {
        return $this->footballData->competitions();
    }

    public function seasons()
    {
        return $this->footballData->seasons();
    }

    public function standings()
    {
        return $this->footballData->standings();
    }

    public function teams()
    {
        return $this->footballData->teams();
    }

    public function matches()
    {
        return $this->footballData->matches();
    }
}
