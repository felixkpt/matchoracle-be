<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;
use App\Models\Season;
use App\Models\Standing;
use App\Models\StandingTable;
use App\Services\Client;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;

class StandingsHandler extends BaseHandlerController
{

    function fetchStandings($competition_id, $season_id = null)
    {

        if ($season_id) {
            $season = Season::find($season_id);
        } else {
            $season = Season::where('competition_id', $competition_id)->where('is_current', true)->first();
        }

        $season_str = Str::before($season->start_date, '-') . '-' . Str::before($season->end_date, '-');

        $competition = Competition::whereHas('gameSources', function ($q) use ($competition_id) {
            $q->where('competition_id', $competition_id);
        })->first();

        if (!$competition) {
            return response(['message' => 'Competition #' . $competition_id . ' not found.'], 404);
        }

        // Access the source_id value for the pivot
        $source = $competition->gameSources->first()->pivot;
        if (!$source) {
            return response(['message' => 'Source for competition #' . $competition_id . ' not found.'], 404);
        }

        if (!$source->is_subscribed) {
            return response(['message' => 'Source #' . $source->source_id . ' not subscribed.'], 402);
        }

        $url = $this->sourceUrl . ltrim($source->source_uri . '/standing/' . $season_str, '/');
        echo ($url) . "\n";

        $content = Client::get($url);
        $crawler = new Crawler($content);

        // Extracted data from the HTML will be stored in this array
        $table_rows = $crawler->filter('.contentmiddle table.standings#standings tr');
        if ($table_rows->count() === 0)
            $table_rows = $crawler->filter('.contentmiddle table.standings#standings-regular-season tr');

        $standings = $table_rows->each(function ($crawler) use (&$seasons) {

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

        if (count($standings) > 0) {
            $competition->type = 'LEAGUE';
            $competition->save();
        }

        // dd($standings);

        $standingsData = $standings;

        $country = $competition->country;

        // Save/update current season
        if ($season) {

            if ($season && $standingsData) {

                // Create or update the standings record
                $standing = Standing::updateOrCreate(
                    [
                        'season_id' => $season->id,
                        'competition_id' => $competition->id
                    ],
                    [
                        'season_id' => $season->id, 'stage' => null, 'type' => null,
                        'competition_id' => $competition->id,
                        'group' => null,
                    ]
                );

                // Insert standings table records
                $this->updateOrCreate($standing, $standingsData, $country, $competition, $season);
            }
        }

        return response(['message' => 'Standings for ' . $competition->name . ' updated.']);
    }

    function updateOrCreate($standing, $standingData, $country, $competition, $season)
    {

        if (count($standingData) > 0 && $competition->type == 'LEAGUE') {
            $competition->has_teams = true;
            $competition->save();
        }

        foreach ($standingData as $tableData) {

            $teamData = $tableData[1];

            $team = (new TeamsHandler())->updateOrCreate($teamData, $country, $competition, $season);

            // Check if the game source with the given ID doesn't exist
            if (!$team->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
                // Attach the relationship with the URI
                $team->gameSources()->attach($this->sourceId, ['source_uri' => $teamData['uri']]);
            }

            // Create or update the standings table record
            StandingTable::updateOrCreate(
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
        }
    }
}
