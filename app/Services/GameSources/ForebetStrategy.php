<?php

namespace App\Services\GameSources;

use App\Services\GameSources\Forebet\ForebetInit;

class ForebetStrategy implements GameSourceStrategyInterface
{
    protected $gameSource;

    public function __construct()
    {
        $this->gameSource = new ForebetInit();
    }

    public function getId()
    {
        return $this->gameSource->sourceId;
    }

    public function initialCompetitions()
    {
        return $this->gameSource->initialCompetitions();
    }

    public function competitions()
    {
        return $this->gameSource->competitions();
    }

    public function seasons()
    {
        return $this->gameSource->seasons();
    }

    public function standings()
    {
        return $this->gameSource->standings();
    }

    public function teams()
    {
        return $this->gameSource->teams();
    }

    public function matches()
    {
        return $this->gameSource->matches();
    }
}
