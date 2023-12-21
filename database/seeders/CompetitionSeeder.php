<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competition;
use App\Services\GameSources\FootballDataStrategy;
use App\Services\GameSources\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;

class CompetitionSeeder extends Seeder
{
    protected $sourceContext;

    function __construct(protected Competition $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();

        // Set the desired game source (can switch between sources dynamically)
        // $this->sourceContext->setGameSourceStrategy(new FootballDataStrategy());
        // $this->sourceContext->initialCompetitions();

        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
        $this->sourceContext->initialCompetitions();

    }

    public function run()
    {

        // $this->sourceContext->initialCompetitions();
    }
}
