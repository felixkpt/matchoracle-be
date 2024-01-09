<?php

namespace App\Services\GameSources;

/**
 * Interface GameSourceStrategyInterface
 * 
 * This interface defines the contract for various strategies used by game sources.
 */
interface GameSourceStrategyInterface
{
    /**
     * Get the unique identifier for the game source strategy.
     * 
     * @return string
     */
    public function getId();

    /**
     * Get the handler for initial competitions data.
     * 
     * @return InitialCompetitionsHandler
     */
    public function initialCompetitionsHandler();

    /**
     * Get the handler for competitions data.
     * 
     * @return CompetitionsHandler
     */
    public function competitionsHandler();

    /**
     * Get the handler for seasons data.
     * 
     * @return SeasonsHandler
     */
    public function seasonsHandler();

    /**
     * Get the handler for standings data.
     * 
     * @return StandingsHandler
     */
    public function standingsHandler();

    /**
     * Get the handler for teams data.
     * 
     * @return TeamsHandler
     */
    public function teamsHandler();

    /**
     * Get the handler for matches data.
     * 
     * @return MatchesHandler
     */
    public function matchesHandler();

    /**
     * Get the handler for single match data.
     * 
     * @return MatchHandler
     */
    public function matchHandler();
}
