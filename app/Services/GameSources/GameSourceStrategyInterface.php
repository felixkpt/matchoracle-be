<?php

namespace App\Services\GameSources;

interface GameSourceStrategyInterface
{
    public function getId();

    public function competitions();

    public function seasons();

    public function standings();

    public function teams();

    public function matches();
}
