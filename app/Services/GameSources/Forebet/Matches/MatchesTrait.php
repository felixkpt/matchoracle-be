<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\Game;
use App\Models\Team;
use App\Services\GameSources\Forebet\TeamsHandler;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait MatchesTrait
{

    function saveGames(&$matches, $competition = null, $season = null)
    {
        $msg = "";
        $saved = $updated = 0;

        $date_or_compe_not_found = [];
        $country_not_found = [];
        $competition_not_found = [];
        $home_team_not_found = [];
        $away_team_not_found = [];

        foreach ($matches as $key => &$match) {

            $competition = $match['competition'] ?? $competition;
            $country = $competition->country ?? null;

            if ($competition && $country && $match['date']) {

                $homeTeam = $this->handleTeam($match['home_team'], $country, $competition, $season, $home_team_not_found);
                $awayTeam = $this->handleTeam($match['away_team'], $country, $competition, $season, $away_team_not_found);


                if ($homeTeam && $awayTeam) {

                    try {

                        DB::beginTransaction();

                        // Update the home_team and away_team IDs in the $matches array
                        $match['home_team']['id'] = $homeTeam->id;
                        $match['away_team']['id'] = $awayTeam->id;

                        // All is set, can save/update game now!
                        $result = $this->saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam);

                        // Check the result of the save operation
                        if ($result === 'saved') {
                            $saved++;
                        } elseif ($result === 'updated') {
                            $updated++;
                        }

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->has_errors = true;
                        $msg = "SaveGames > Error during data import for compe#$competition->id: ";

                        Log::channel($this->logChannel)->error($msg . $e->getMessage() . ', File: ' . $e->getFile() . ', Line no:' . $e->getLine());
                    }
                } else {

                    if (!$homeTeam) {

                        if (!isset($home_team_not_found[$country->name])) {
                            $home_team_not_found[$match['home_team']['name']] = 1;
                        } else {
                            $home_team_not_found[$match['home_team']['name']] = $home_team_not_found[$match['home_team']['name']] + 1;
                        }

                        Log::channel($this->logChannel)->critical('HomeTeam not found:', (array) $match['home_team']['name']);
                    }

                    if (!$awayTeam) {

                        if (!isset($away_team_not_found[$country->name])) {
                            $away_team_not_found[$match['away_team']['name']] = 1;
                        } else {
                            $away_team_not_found[$match['away_team']['name']] = $away_team_not_found[$match['away_team']['name']] + 1;
                        }

                        Log::channel($this->logChannel)->critical('AwayTeam not found:', (array) $match['away_team']['name']);
                    }
                }
            } else {
                $no_date_mgs = ['competition' => $competition->id ?? null, 'season' => $season ? $season->id : null, 'match' => $match];
                $date_or_compe_not_found['match'][$key] = $match;
                Log::channel($this->logChannel)->critical('Match has no date or competition:', $no_date_mgs);
            }

            sleep(0);
        }

        $msg = "Fetching matches completed, (saved $saved, updated: $updated).";

        if (count($date_or_compe_not_found) > 0) {
            $msg .= ' ' . count($date_or_compe_not_found) . ' dates / competition were not found.';
        }

        if (count($country_not_found) > 0) {
            $msg .= ' ' . count($country_not_found) . ' countries were not found.';
        }

        if (count($competition_not_found) > 0) {
            $msg .= ' ' . count($competition_not_found) . ' competitions were not found.';
        }

        if (count($home_team_not_found) > 0) {
            $msg .= ' ' . count($home_team_not_found) . ' home teams were not found.';
        }

        if (count($away_team_not_found) > 0) {
            $msg .= ' ' . count($away_team_not_found) . ' away teams were not found.';
        }

        return [$saved, $updated, $msg];
    }

    private function handleTeam($teamData, $country, $competition, $season, &$teamNotFound)
    {

        $team = Team::whereHas('gameSources', function ($q) use ($teamData) {
            $q->where('source_uri', $teamData['uri']);
        })->first();

        if (!$team) {
            $team = (new TeamsHandler())->updateOrCreate($teamData, $country, $competition, $season, true);
            if (!$team) {
                if (!isset($teamNotFound[$country->name])) {
                    $teamNotFound[$teamData['name']] = 1;
                } else {
                    $teamNotFound[$teamData['name']]++;
                }

                Log::channel($this->logChannel)->critical('Team not found:', (array) $teamData['name']);
            }
        }

        return $team;
    }

    function saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam)
    {
        // Extracting necessary information for creating or updating a game
        $competition_id = $competition->id;
        $season_id = $season->id ?? null;
        $country_id = $country->id;
        $date = Carbon::parse($match['date'] . ' ' . $match['time']);
        $utc_date = $date->format('Y-m-d H:i');

        $has_time = $match['has_time'];
        $status = $date->isFuture() ? 'SCHEDULED' : (Str::contains($match['date'], ':') ? 'PENDING' : 'FINISHED');
        $matchday = null;
        $stage = null;
        $group = null;
        $status_id = activeStatusId();
        $user_id = auth()->id();

        // Prepare data array for creating or updating a game
        $arr = [
            'competition_id' => $competition_id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'country_id' => $country_id,
            'date' => $date->format('Y-m-d'),
            'status' => $status,
            'matchday' => $matchday,
            'stage' => $stage,
            'group' => $group,
            'status_id' => $status_id,
            'user_id' => $user_id,
        ];

        if ($season_id) {
            $arr['season_id'] = $season_id;
        }

        $qry = [
            ['home_team_id', $homeTeam->id],
            ['away_team_id', $awayTeam->id],
        ];

        // Check if a game with the same details already exists
        $game = Game::query()
            ->whereDate('date', $date->format('Y-m-d'))
            ->where($qry)->first();

        // $qry['competition_id'] = $competition_id;

        // If the game exists, update it; otherwise, create a new one
        if ($game) {

            if ($has_time) {
                $arr['utc_date'] = $utc_date;
                $arr['has_time'] = $has_time;
            } else if (!$game->has_time) {
                $arr['utc_date'] = $utc_date;
                $arr['has_time'] = $has_time;
            }

            $game->update($arr);
            $msg = 'updated';
        } else {
            $arr['utc_date'] = $utc_date;
            $arr['has_time'] = $has_time;
            $arr['game_score_status_id'] = gameScoresStatus('scheduled');
            $game = Game::create($arr);
            $msg = 'saved';
        }

        // Attach game source information to the game if not already attached
        $game_details_uri = $match['game_details']['uri'];

        // Check if the entry already exists in the pivot table
        $query = $game->gameSources()->where('game_source_id', $this->sourceId);
        if (!$query->exists()) {
            $game->gameSources()->attach($this->sourceId, ['source_uri' => $game_details_uri]);
        } elseif ($query->whereNull('source_uri')->exists()) {
            // If the entry exists but the source_uri is NULL, update the source_uri
            $query->update(['source_uri' => $game_details_uri]);
        }

        // Synchronize referees
        $this->syncReferees($game, $match);

        if ($game) {
            $this->storeScores($game, $match['game_details']);
        }

        // Return a message indicating whether the game was saved or updated
        return $msg;
    }
}
