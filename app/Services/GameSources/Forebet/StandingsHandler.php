<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Standing;
use App\Models\StandingTable;
use App\Services\ClientHelper\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class StandingsHandler
{
    use ForebetInitializationTrait, CompetitionAbbreviationsTrait;

    protected $has_errors = false;
    /**
     * Constructor for the CompetitionsHandler class.
     * 
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
     * Constructs a URL based on the source URL and additional parameters.
     *
     * @param string $path The path to be appended to the source URL.
     * @return string The constructed URL.
     */
    private function constructUrl($path)
    {
        return $this->sourceUrl . ltrim($path, '/');
    }

    /**
     * Fetches standings data for a given competition and season.
     *
     * @param int $competition_id The ID of the competition.
     * @param int|null $season_id (optional) The ID of the season.
     * @return \Illuminate\Http\Response|array The response with the updated standings information.
     */
    function fetchStandings($competition_id, $season_id = null)
    {
        // Prepare data for fetching
        $results = $this->prepareFetch($competition_id, $season_id);

        // If preparation is successful, extract necessary data
        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else {
            // Return error message if preparation fails
            return $results;
        }

        // Create or update competition abbreviation
        $this->createCompetitionAbbreviation($competition, $this->constructUrl($source->source_uri));

        // Construct URL for fetching standings
        $url = $this->constructUrl($source->source_uri . '/standing/' . $season_str);

        // Fetch HTML content from the URL
        $content = Client::get($url);
        if (!$content) {
            // Return error message if content retrieval fails
            return $this->matchMessage('Source not accessible or not found.', 504);
        }

        // Parse HTML content using Symfony DomCrawler
        $crawler = new Crawler($content);

        Log::channel($this->logChannel)->info("Getting standings for compe #{$competition->id} ...");
        if (strpos($crawler->text(), 'Attention Required!') !== false) {
            $message = "Attention Required! Blocked while getting standings for compe #{$competition->id}";
            Log::channel($this->logChannel)->critical($message);
            return $this->matchMessage($message, 500);
        }


        // Extract standings tables from HTML
        $tables = $crawler->filter('.contentmiddle table.standings#standings');
        if ($tables->count() === 0)
            $tables = $crawler->filter('.contentmiddle table.standings#standings-regular-season');
        if ($tables->count() === 0) {
            $tables = $crawler->filter('.contentmiddle table.standings[id^=standings-group-]');
        }

        $winner = null;
        $saved = $updated = 0;

        // If there is only one table, directly handle it
        if ($tables->count() === 1) {
            $adjacentDiv = $tables->previousAll()->first();
            $k = $adjacentDiv->filter('h4');
            $title = null;
            if ($k->count() > 0)
                $title = $k->text();

            $type = $title;

            // Handle fetching for single table
            [$saved, $updated, $winner] = $this->handleFetchStandings($competition, $season, $tables, null, null, $type);
        } else {
            // If there are multiple tables, iterate over each one
            $tables->each(function ($table) use ($competition, $season, &$saved, &$updated, &$winner) {
                // Get the adjacent div before the table
                $adjacentDiv = $table->previousAll()->first();
                $k = $adjacentDiv->filter('h4');
                $title = null;
                if ($k->count() > 0)
                    $title = $k->text();

                $stage = null;
                $group = null;
                if (Str::contains($title, 'Group')) {
                    $group = $title;
                } else {
                    $stage = $title;
                }

                // Handle fetching for each table
                [$saved_new, $updated_new, $winner] = $this->handleFetchStandings($competition, $season, $table, $stage, $group);
                $saved = $saved + $saved_new;
                $updated = $updated + $updated_new;
            });
        }

        // Update season fetched standings status if needed
        if ($saved + $updated > 0 && $season && !$season->is_current && Carbon::parse($season->end_date)->isPast()) {
            $season->update(['fetched_standings' => true]);
        }

        // Prepare response message
        $message = 'Standings for ' . $competition->name . ', season ' . Carbon::parse($season->start_date)->format('Y') . '/' . Carbon::parse($season->end_date)->format('Y') . ' updated. ';
        $message .= $saved . ' new added, and ' . $updated . ' existing updated. (winner ' . ($winner ? $winner->name : 'N/A') . ')';

        // Prepare response data
        $response = [
            'message' => $message,
            'results' => ['created_counts' => $saved, 'updated_counts' => $updated,  'failed_counts' => 0],
            'status' => $saved > 0 ? 200 : 201,
        ];

        // If response is requested without HTTP response, return data array
        if (request()->without_response) return $response;

        // Otherwise, return HTTP response
        return response($response);
    }

    private function handleFetchStandings($competition, $season, $table, $stage = null, $group = null, $type = null)
    {

        $standings = $table->filter('tr')->each(function ($crawler) {

            if ($crawler->count() > 0) {
                $heading = $crawler->filter('.heading');
                if ($heading->count() == 0) {

                    $centers = $crawler->filter('td')->each(function ($crawler) {

                        if ($crawler->count() > 0) {

                            if ($crawler->attr('class') == 'std_pos') {
                                return $crawler->text();
                            } else if ($crawler->attr('class') == 'standing-second-td') {
                                return ['name' => $crawler->filter('a')->text(), 'uri' => $crawler->filter('a')->attr('href')];
                            } else {
                                return $crawler->text();
                            }
                        } else return null;
                    });

                    return $centers;
                }
            }
        });

        $standings = array_values(array_filter($standings));

        if (count($standings) > 0 && !$group) {
            $competition->type = 'LEAGUE';
            $competition->save();
        }

        $standingsData = $standings;

        $country = $competition->country;

        $saved = $updated = 0;
        $winner = null;
        // Save/update current season
        if ($season) {

            if ($season && $standingsData) {

                // Create or update the standings record
                $standing = Standing::updateOrCreate(
                    [
                        'competition_id' => $competition->id,
                        'season_id' => $season->id,
                        'stage' => $stage,
                        'group' => $group,
                        'type' => $type,
                    ],
                    [
                        'competition_id' => $competition->id,
                        'season_id' => $season->id,
                        'stage' => $stage,
                        'group' => $group,
                        'type' => $type,
                        'updated_at' => now(),
                    ]
                );

                // Insert standings table records
                [$saved, $updated, $winner] = $this->updateOrCreate($standing, $standingsData, $country, $competition, $season);
            }
        }

        return [$saved, $updated, $winner];
    }

    function updateOrCreate($standing, $standingData, $country, $competition, $season)
    {

        if (count($standingData) > 0 && $competition->type == 'LEAGUE') {
            $competition->has_teams = true;
            $competition->save();
        }

        $saved = $updated = 0;
        $winner = null;
        foreach ($standingData as $tableData) {

            if (!isset($tableData[1]) || !is_array($tableData[1])) continue;

            $position = $tableData[0];
            $teamData = $tableData[1];

            $team = (new TeamsHandler())->updateOrCreate($teamData, $country, $competition, $season, false, $position);

            if (!$team) {
                Log::channel($this->logChannel)->critical("Team could not be created for compe: {$competition->id}, season {$season->id}", [$tableData]);
                continue;
            }

            // Create or update the standings table record
            $res = StandingTable::updateOrCreate(
                [
                    'standing_id' => $standing->id,
                    'team_id' => $team->id,
                ],
                [
                    'standing_id' => $standing->id,
                    'team_id' => $team->id,
                    'season_id' => $season->id,
                    'position' => $tableData[0],
                    'points' => $tableData[2],
                    'played_games' => $tableData[3],
                    'won' => $tableData[4],
                    'draw' => $tableData[5],
                    'lost' => $tableData[6],
                    'goals_for' => $tableData[7],
                    'goals_against' => $tableData[8],
                    'goal_difference' => $tableData[9],
                ]
            );

            if ($res->wasRecentlyCreated) $saved++;
            else $updated++;

            // Season winner determination and saving
            if (!$winner) {
                $new_winner = $this->updateSeasonWinner($competition, $season, $team);
                if ($new_winner) {
                    $winner = $new_winner;
                }
            }
        }

        return [$saved, $updated, $winner];
    }

    private function updateSeasonWinner($competition, $season, $team)
    {
        $winner = $season->winner ?? null;
        if (!$season->winner_id && $competition->type == 'LEAGUE') {

            $season_games_counts = $season->games()->count();
            // Check if it's not the current season or the number of games played matches the expected games per season
            if (!$season->is_current || ($season_games_counts > 0 && $season_games_counts == $competition->games_per_season)) {

                // If a team is successfully updated or created
                if ($team) {
                    $winner = $team;
                    $season->winner_id = $team->id;
                    $season->save(); // Save the winner information in the season
                }
            }
        }

        return $winner;
    }
}
