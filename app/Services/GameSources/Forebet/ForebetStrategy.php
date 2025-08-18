<?php

namespace App\Services\GameSources\Forebet;

use App\Services\GameSources\Forebet\Matches\MatchesHandler;
use App\Services\GameSources\Forebet\Matches\MatchHandler;
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
     * @property string $jobId          The unique identifier for the job.
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct($jobId = null)
    {
        $this->initialize();
        $this->jobId = $jobId;
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
        return new InitialCompetitionsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function competitionAbbreviationsHandler()
    {
        // Method to get the handler for competitions abbreviations data.
        return new CompetitionAbbreviationsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function competitionsHandler()
    {
        // Method to get the handler for competitions data.
        return new CompetitionsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function seasonsHandler()
    {
        // Method to get the handler for seasons data.
        return new SeasonsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function standingsHandler()
    {
        // Method to get the handler for standings data.
        return new StandingsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function teamsHandler()
    {
        // Method to get the handler for teams data.
        return new TeamsHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesHandler()
    {
        // Method to get the handler for matches data.
        return new MatchesHandler($this->jobId);
    }

    /**
     * {@inheritdoc}
     */
    public function matchHandler()
    {
        // Method to get the handler for single match data.
        return new MatchHandler($this->jobId);
    }
}
