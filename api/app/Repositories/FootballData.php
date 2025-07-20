<?php

namespace App\Repositories;

use App\Models\GameSource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * This service class encapsulates football-data.org's RESTful API.
 *
 * @author Daniel Freitag <daniel@football-data.org>
 * @date 04.11.2015 | switched to v2 09.08.2018 | migrated to v4 03.05.2022
 * 
 */
class FootballData
{

    public $config;
    public $baseUri;
    public $reqPrefs = array();
    public $sourceId;

    public function __construct()
    {
        $this->config['baseUri'] = env('baseUri');
        $this->config['authToken'] = env('authToken');

        // some lame hint for the impatient
        if ($this->config['authToken'] == 'YOUR_AUTH_TOKEN' || !isset($this->config['authToken'])) {
            exit('Get your API-Key first and edit config.ini');
        }

        $this->baseUri = $this->config['baseUri'];

        $this->reqPrefs['http']['method'] = 'GET';
        $this->reqPrefs['http']['header'] = 'X-Auth-Token: ' . $this->config['authToken'];

        $url = 'https://www.football-data.org/';

        $this->sourceId = GameSource::updateOrCreate([
            'url' => $url,
        ], [
            'name' => 'The dev-friendly football API',
            'url' => $url,
            'description' => 'The dev-friendly football API - RESTful. Reliable. Free to use. Easy to integrate.',
            'priority_number' => 1,
            'status_id' => activeStatusId(),
            'user_id' => auth()->id() ?? 0,
        ])->id ?? 0;
    }

    /**
     * Function returns a particular competition identified by an id.
     * 
     * @param Integer $id
     * @return object
     */
    public function findCompetitionById($id)
    {
        $resource = 'competitions/' . $id;
        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    /**
     * Function returns all available matches for a given date range.
     * 
     * @param DateString 'Y-m-d' $start
     * @param DateString 'Y-m-d' $end
     * 
     * @return array of matches
     */
    public function findMatchesForDateRange($start, $end)
    {
        $resource = 'matches/?dateFrom=' . $start . '&dateTo=' . $end;

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findMatchesByCompetitionAndSeason($c, $season = null, $matchday = null)
    {

        $resource = 'competitions/' . $c . '/matches/?';

        if (!is_null($season)) {
            $resource .= 'season=' . $season . '&';
        }

        if (!is_null($matchday)) {
            $resource .= 'matchday=' . $matchday . '&';
        }

        // Remove trailing '&' if there are parameters
        $resource = rtrim($resource, '&');

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findMatchesByCompetitionWithDateRange($c, $start = null, $end = null)
    {
        $resource = 'competitions/' . $c . '/matches/?';

        // Add parameters if they exist
        if (!is_null($start)) {
            $resource .= 'dateFrom=' . $start . '&';
        }

        if (!is_null($end)) {
            $resource .= 'dateTo=' . $end . '&';
        }

        // Remove trailing '&' if there are parameters
        $resource = rtrim($resource, '&');

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findMatchesByCompetition($c, $season = null, $matchday = null)
    {

        $resource = 'competitions/' . $c . '/matches?';

        $q = '';
        if ($season) {
            $q .= 'season=' . $season . '&';
        }
        if ($matchday) {
            $q .= 'matchday=' . $matchday . '&';
        }

        // Remove trailing '&' if there are parameters
        $resource = rtrim($resource, '&');

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findMatchesByCompetitionAndMatchday($c, $m)
    {
        $resource = 'competitions/' . $c . '/matches/?matchday=' . $m;

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findStandingsByCompetition($id, $season = null)
    {
        $resource = 'competitions/' . $id . '/standings';
        if ($season) {
            $resource = $resource . '?season=' . $season;
        }

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    public function findHomeMatchesByTeam($teamId)
    {
        $resource = 'teams/' . $teamId . '/matches/?venue=HOME';
        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response)->matches;
    }

    /**
     * Function returns one unique match identified by a given id.
     * 
     * @param int $id
     * @return stdObject fixture
     */
    public function findMatchById($id)
    {
        $resource = 'matches/' . $id;
        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    /**
     * Function returns one unique team identified by a given id.
     * 
     * @param int $id
     * @return stdObject team
     */
    public function findTeamById($id)
    {
        $resource = 'teams/' . $id;
        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    /**
     * Function returns all teams matching a given keyword.
     * 
     * @param string $keyword
     * @return list of team objects
     */
    public function searchTeam($keyword)
    {
        $resource = 'teams/?name=' . $keyword;
        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }

    /**
     * Function returns Head to Head for meeting teams.
     * 
     * @param int $matchId
     * @param int $limit
     * @return list of matches objects
     */
    public function head2head($matchId, $limit = 10)
    {
        $resource = 'matches/' . $matchId . '/head2head?limit=' . $limit;

        $response = file_get_contents(
            $this->baseUri . $resource,
            false,
            stream_context_create($this->reqPrefs)
        );

        return json_decode($response);
    }
}
