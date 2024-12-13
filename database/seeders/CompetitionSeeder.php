<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;

class CompetitionSeeder extends Seeder
{
    protected $sourceContext;

    public function __construct()
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    public function run()
    {

        $this->sourceContext->initialCompetitionsHandler()->seedCompetitions();
    }
}
