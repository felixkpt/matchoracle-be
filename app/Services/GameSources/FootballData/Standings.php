<?php

namespace App\Services\GameSources\FootballData;

use App\Models\StandingTable;
use App\Repositories\FootballData;

class Standings
{
    public $api;

    public function __construct()
    {
        $this->api = new FootballData();
    }

    function updateOrCreate($standing, $standingData, $country, $competition, $season)
    {

        if ($competition->type == 'LEAGUE' && count($standingData->table) > 0) {
            $competition->has_teams = true;
            $competition->save();
        }

        foreach ($standingData->table as $tableData) {
            $teamData = $tableData->team;

            $team = app(Teams::class)->updateOrCreate($teamData, $country, $competition);

            // Check if the game source with the given ID doesn't exist
            if (!$team->gameSources()->where('game_source_id', $this->api->sourceId)->exists()) {
                // Attach the relationship with the URI
                $team->gameSources()->attach($this->api->sourceId, ['source_id' => $teamData->id]);
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
                    'position' => $tableData->position,
                    'played_games' => $tableData->playedGames,
                    'won' => $tableData->won,
                    'draw' => $tableData->draw,
                    'lost' => $tableData->lost,
                    'points' => $tableData->points,
                    'goals_for' => $tableData->goalsFor,
                    'goals_against' => $tableData->goalsAgainst,
                    'goal_difference' => $tableData->goalDifference,
                ]
            );
        }
    }
}
