<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Standing;
use App\Models\StandingTable;
use App\Services\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class StandingsHandler
{
    use ForebetInitializationTrait;

    protected $has_errors = false;
    /**
     * Constructor for the CompetitionsHandler class.
     * 
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();
    }

    function fetchStandings($competition_id, $season_id = null)
    {

        $results = $this->prepareFetch($competition_id, $season_id);

        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else return $results;

        $url = $this->sourceUrl . ltrim($source->source_uri . '/standing/' . $season_str, '/');

        $content = Client::get($url);
        if (!$content) return $this->matchMessage('Source not accessible or not found.');

        $crawler = new Crawler($content);

        // Extracted data from the HTML will be stored in this array
        $tables = $crawler->filter('.contentmiddle table.standings#standings');

        if ($tables->count() === 0)
            $tables = $crawler->filter('.contentmiddle table.standings#standings-regular-season');

        // Let us check if the counts = 0 then we try with group strategy
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

                [$saved_new, $updated_new, $winner] = $this->handleFetchStandings($competition, $season, $table, $stage, $group);
                $saved = $saved + $saved_new;
                $updated = $updated + $updated_new;
            });
        }

        if ($saved + $updated > 0 && $season && !$season->is_current && Carbon::parse($season->end_date)->isPast()) {
            $season->update(['fetched_standings' => true]);
        }

        $message = 'Standings for ' . $competition->name . ', season ' . Carbon::parse($season->start_date)->format('Y') . '/' . Carbon::parse($season->end_date)->format('Y') . ' updated. ';
        $message .= $saved . ' new standings added, and ' . $updated . ' existing standings updated. (winner ' . ($winner ? $winner->name : 'N/A') . ')';

        $response = ['message' => $message, 'results' => ['saved_updated' => $saved + $updated]];

        if (request()->without_response) return $response;

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

            if (!isset($tableData[1])) continue;

            $teamData = $tableData[1];

            $team = (new TeamsHandler())->updateOrCreate($teamData, $country, $competition, $season);

            if (!$team) {
                Log::critical("Team could not be created for compe: {$competition->id}, season {$season->id}", [$teamData]);
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
