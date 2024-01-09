<?php

namespace App\Services\GameSources\Forebet;

use App\Services\GameSources\GameSourceStrategyInterface;

/**
 * Class ForebetStrategy
 * 
 * Implementation of the GameSourceStrategyInterface for Forebet game source.
 */
class ForebetStrategy implements GameSourceStrategyInterface
{
    use ForebetInitializationTrait;
    /**
     * Constructor for the ForebetStrategy class.
     * 
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        // Method to get the identifier for the Forebet game source.
        return $this->sourceId;
    }

    /**
     * {@inheritdoc}
     */
    public function initialCompetitionsHandler()
    {
        // Method to get the handler for initial competitions data.
        return new InitialCompetitionsHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function competitionsHandler()
    {
        // Method to get the handler for competitions data.
        return new CompetitionsHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function seasonsHandler()
    {
        // Method to get the handler for seasons data.
        return new SeasonsHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function standingsHandler()
    {
        // Method to get the handler for standings data.
        return new StandingsHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function teamsHandler()
    {
        // Method to get the handler for teams data.
        return new TeamsHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function matchesHandler()
    {
        // Method to get the handler for matches data.
        return new MatchesHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function matchHandler()
    {
        // Method to get the handler for single match data.
        return new MatchHandler();
    }
}
