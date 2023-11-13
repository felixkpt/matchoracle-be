<?php

namespace App\Services\GameSources;

class GameSourceStrategy
{
    protected $gameSourceStrategyInterface;

    public function setGameSourceStrategy(GameSourceStrategyInterface $strategy)
    {
        $this->gameSourceStrategyInterface = $strategy;
    }

    public function getId()
    {
        return $this->gameSourceStrategyInterface->getId();
    }

    public function competitions()
    {
        return $this->gameSourceStrategyInterface->competitions();
    }

    public function seasons()
    {
        return $this->gameSourceStrategyInterface->seasons();
    }

    public function standings()
    {
        return $this->gameSourceStrategyInterface->standings();
    }

    public function teams()
    {
        return $this->gameSourceStrategyInterface->teams();
    }

    public function matches()
    {
        return $this->gameSourceStrategyInterface->matches();
    }

}
