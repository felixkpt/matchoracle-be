<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;

class CompetitionAbbreviationsHandler
{
    use ForebetInitializationTrait, CompetitionAbbreviationsTrait;

    protected $jobId;

    /**
     * Constructor for the CompetitionsHandler class.
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();

        if (!$this->jobId) {
            $this->jobId = str()->random(6);
        }
    }

    /**
     * Fetch competitions without abbreviations and create them if possible.
     */
    public function fetch($competition_id)
    {

        $results = $this->prepareFetch($competition_id);

        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else return $results;

        $uri = $source->source_uri;
        $url = $this->sourceUrl . ltrim($uri, '/');
        return $this->createCompetitionAbbreviation($competition, $url);
    }
}
