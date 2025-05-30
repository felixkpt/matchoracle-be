<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;

class CompetitionAbbreviationsSeeder extends Seeder
{
    protected $sourceContext;

    function __construct()
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function run()
    {
        $this->sourceContext->competitionAbbreviationsHandler()->fetch();
    }
}
