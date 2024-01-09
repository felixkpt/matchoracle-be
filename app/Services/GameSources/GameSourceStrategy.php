<?php

namespace App\Services\GameSources;

/**
 * Class GameSourceStrategy
 *
 * A context class that uses a strategy to perform various operations related to game sources.
 */
class GameSourceStrategy
{
    /**
     * @var GameSourceStrategyInterface The strategy instance.
     */
    protected $gameSourceStrategyInterface;

    /**
     * Set the game source strategy.
     *
     * @param GameSourceStrategyInterface $strategy The strategy instance.
     */
    public function setGameSourceStrategy(GameSourceStrategyInterface $strategy)
    {
        $this->gameSourceStrategyInterface = $strategy;
    }

    /**
     * Get the unique identifier for the game source strategy.
     *
     * @return string
     * @inheritdoc
     */
    public function getId()
    {
        return $this->gameSourceStrategyInterface->getId();
    }

    /**
     * Get the handler for initial competitions data.
     *
     * @return InitialCompetitionsHandler
     * @inheritdoc
     */
    public function initialCompetitionsHandler()
    {
        return $this->gameSourceStrategyInterface->initialCompetitionsHandler();
    }

    /**
     * Get the handler for competitions data.
     *
     * @return CompetitionsHandler
     * @inheritdoc
     */
    public function competitionsHandler()
    {
        return $this->gameSourceStrategyInterface->competitionsHandler();
    }

    /**
     * Get the handler for seasons data.
     *
     * @return SeasonsHandler
     * @inheritdoc
     */
    public function seasonsHandler()
    {
        return $this->gameSourceStrategyInterface->seasonsHandler();
    }

    /**
     * Get the handler for standings data.
     *
     * @return StandingsHandler
     * @inheritdoc
     */
    public function standingsHandler()
    {
        return $this->gameSourceStrategyInterface->standingsHandler();
    }

    /**
     * Get the handler for teams data.
     *
     * @return TeamsHandler
     * @inheritdoc
     */
    public function teamsHandler()
    {
        return $this->gameSourceStrategyInterface->teamsHandler();
    }

    /**
     * Get the handler for matches data.
     *
     * @return MatchesHandler
     * @inheritdoc
     */
    public function matchesHandler()
    {
        return $this->gameSourceStrategyInterface->matchesHandler();
    }

    /**
     * Get the handler for single match data.
     *
     * @return MatchHandler
     * @inheritdoc
     */
    public function matchHandler()
    {
        return $this->gameSourceStrategyInterface->matchHandler();
    }
}
