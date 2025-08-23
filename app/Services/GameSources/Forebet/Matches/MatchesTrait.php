<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\Game;
use App\Models\Team;
use App\Services\Common;
use App\Services\GameSources\Forebet\TeamsHandler;
use Carbon\Carbon;
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
                        $class_name = class_basename($this);
                        $msg = "SaveGames $class_name > Error during data import for compe#$competition->id: ";

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
            $team = (new TeamsHandler($this->jobId))->updateOrCreate($teamData, $country, $competition, $season, true);
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

    private function saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam, $isSingleMatchJob = false)
    {
        if (!$competition) {
            $msg = 'MatchesTrait.saveGame >> Game cannot be saved without competition';
            Log::channel($this->logChannel)->info($msg, ['games' => $match]);
            return $msg;
        }

        [$game, $msg] = $this->createOrUpdateGame($match, $country, $competition, $season, $homeTeam, $awayTeam);

        // Attach game sources
        $game_details_uri = $match['game_details']['uri'];
        $query = $game->gameSources()->where('game_source_id', $this->sourceId);
        if (!$query->exists()) {
            $game->gameSources()->attach($this->sourceId, ['source_uri' => $game_details_uri]);
        } elseif ($query->whereNull('source_uri')->exists()) {
            $game->gameSources()->updateExistingPivot($this->sourceId, [
                'source_uri' => $game_details_uri
            ]);
        }

        // Synchronize referees and scores
        $this->syncReferees($game, $match);
        $this->storeScores($game, $match['game_details'], $isSingleMatchJob);

        // Run duplicate cleanup last
        // $this->deleteDuplicates($game);

        return $msg;
    }

    public function updateGame($game, $data, $isSingleMatchJob = false)
    {

        Common::saveTeamLogo($game['homeTeam'], $data['home_team_logo']);
        Common::saveTeamLogo($game['awayTeam'], $data['away_team_logo']);

        $stadium = Common::saveStadium($data['stadium']);
        $weather_condition = Common::saveWeatherCondition($data['weather_condition']);

        $competition = !empty($game['competition_id']) ? $game->competition : $game['competition'];

        if ($game) {
            $game_utc_date = $game->utc_date;
            $game_results_status = $game->game_score_status_id;

            // common columns during create and update
            $arr = [
                'utc_date' => $data['utc_date'],
                'has_time' => $data['has_time'],
                'temperature' => $data['temperature'],
                'last_fetch' => now(),
            ];

            $results_status = gameScoresStatus('scheduled');
            if ($data['full_time_results'] || $data['postponed'] || $data['cancelled']) {
                $results_status = $this->storeScores($game, $data, $isSingleMatchJob);
            }

            if ($stadium) {
                $arr['stadium_id'] = $stadium->id;
            }

            if ($weather_condition) {
                $arr['weather_condition_id'] = $weather_condition->id;
            }

            $msg = "Game #{$game->id} updated successfully, (results status " . ($results_status > -1 ? ($game_results_status . ' > ' . $results_status) : 'unchanged') . ').';

            if (Carbon::parse($data['utc_date'])->isFuture()) {
                $msg = "Game #{$game->id} fixture updated successfully.";
            }

            if (!Str::startsWith($game_utc_date, $data['utc_date'])) {
                $has_time = $data['has_time'] ? 'Yes' : 'No';
                $msg .= ' Time updated (' . $game_utc_date . ' > ' . $data['utc_date'] . '), Has time: ' . $has_time;
            }

            $game->update($arr);

            // add abbr if not exists
            $this->handleCompetitionAbbreviation($competition);

            return $msg;
        } else {
            // delete fixture, date changed
        }
    }

    private function deactivateGames($game, $matches, $playing = 'home')
    {
        return;

        $team = $game->homeTeam;
        if ($playing == 'away') {
            $team = $game->homeTeam;
        }

        // echo "Team: {$team->name}<br>";

        $latest_date = Carbon::parse($game->utc_date)->subDay()->format('Y-m-d');
        $old_date = array_pop($matches)['date'];

        $team_games = Game::query()
            ->where('status_id', activeStatusId())
            ->whereIn('game_score_status_id', unsettledGameScoreStatuses())
            ->where('date', '>=', $old_date)
            ->where('date', '<=', $latest_date)
            ->where(function ($q) use ($team) {
                $q->where('home_team_id', $team->id)
                    ->orWhere('away_team_id', $team->id);
            })
            ->get();


        foreach ($team_games as $team_game) {
            $gameDate = Carbon::parse($team_game->utc_date)->format('Y-m-d');
            // if ($gameDate == '2023-09-26') {
            // echo $team_game->id . "<br>";
            $gameCompetition = $team_game->competition;

            $matchFound = false;
            foreach ($matches as $match) {

                $d = Carbon::parse($match['date'])->format('Y-m-d');
                $playingFound = $match['home_team']['id'] == $team_game->home_team_id  && $match['away_team']['id'] == $team_game->away_team_id;

                if ($d === $gameDate && $match['competition']->id === $gameCompetition->id && $playingFound) {
                    $matchFound = true;
                    break;
                }
            }

            if (!$matchFound) {
                $team_game->update([
                    'status_id' => inActiveStatusId(),
                    'game_score_status_id' => gameScoresStatus('Deactivated'),
                    'status' => 'Deactivated'
                ]);
            }
            // } else {
            //     echo 'Skipping...' . $team_game->id . '<br>';
            // }
        }
    }

    private function createOrUpdateGame($match, $country, $competition, $season, $homeTeam, $awayTeam)
    {
        $competition_id = $competition->id;
        $season_id = $season->id ?? null;
        $country_id = $country->id;
        $date = $match['date'];
        $utc_date = $match['utc_date'];
        $has_time = $match['has_time'];

        $parsed_date = Carbon::parse($utc_date);

        if ($parsed_date->isFuture()) {
            $status = 'SCHEDULED';
        } else {
            $status = Str::contains($match['date'], ':') ? 'PENDING' : 'FINISHED';
        }

        $data = [
            'competition_id' => $competition_id,
            'season_id'      => $season_id ? $season_id : null,
            'home_team_id'   => $homeTeam->id,
            'away_team_id'   => $awayTeam->id,
            'country_id'     => $country_id,
            'date'           => $date,
            'matchday'       => null,
            'stage'          => null,
            'group'          => null,
            'status'         => $status,
            'status_id'      => activeStatusId(),
            'user_id'        => auth()->id(),
        ];

        $game = Game::query()
            ->whereDate('date', $date)
            ->where('home_team_id', $homeTeam->id)
            ->where('away_team_id', $awayTeam->id)->first();

        if ($game) {
            if ($has_time || !$game->has_time) {
                $data['utc_date'] = $utc_date;
                $data['has_time'] = $has_time;
            }
            // Remove nulls only
            $data = array_filter($data, fn($v) => !is_null($v));
            $game->update($data);
            $msg = 'updated';
        } else {
            $data['utc_date'] = $utc_date;
            $data['has_time'] = $has_time;
            $data['game_score_status_id'] = gameScoresStatus('scheduled');
            $game = Game::create($data);
            $msg = 'saved';
        }

        return [$game, $msg];
    }

    function handleCompetitionAbbreviation($competition)
    {
        if ($competition) {
            // $arr['competition_id'] = $competition->id;

            // $abbrv = CompetitionAbbreviation::where('name', $game['competition_abbreviation'])->wherenull('competition_id');

            // if ($abbrv->count() === 1) {
            //     $abbrv->update(['competition_id' => $competition->id]);
            //     $competition->update(['abbreviation' => $game['competition_abbreviation']]);

            //     $msg = 'Fixture updated -- ' . $game['competition_abbreviation'] . ' abbrv tagged';
            // }
        }
    }

    private function deleteDuplicates($game)
    {
        $class_name = class_basename($this);
        $parsed_date = Carbon::parse($game->utc_date);

        // 1. Remove exact same date duplicates except current game
        $duplicates = Game::query()
            ->whereDate('date', $game->date)
            ->where('home_team_id', $game->home_team_id)
            ->where('away_team_id', $game->away_team_id)
            ->where('id', '!=', $game->id)
            ->get();

        if ($duplicates->isNotEmpty()) {
            Log::channel($this->logChannel)->info('MatchesTrait.deleteDuplicates >> Same date duplicates removed:', [
                'Game in question' => $game->id,
                'duplicates'       => $duplicates->pluck('id'),
            ]);
            $duplicates->each->delete();
        }

        // 2. Remove games within ±2 weeks if unsettled
        $startDate = $parsed_date->copy()->subWeeks(2)->format('Y-m-d');
        $endDate = $parsed_date->copy()->addWeeks(2)->format('Y-m-d');

        $nearbyDuplicates = Game::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('home_team_id', $game->home_team_id)
            ->where('away_team_id', $game->away_team_id)
            ->whereIn('game_score_status_id', unsettledGameScoreStatuses())
            ->where('id', '!=', $game->id)
            ->get();

        if ($nearbyDuplicates->isNotEmpty()) {
            Log::channel($this->logChannel)->info('MatchesTrait.deleteDuplicates >> Nearby unsettled games removed:', [
                'Class name'       => $class_name,
                'Game in question' => $game->id,
                'duplicates'       => $nearbyDuplicates->pluck('id')->toArray(),
                'startDate'        => $startDate,
                'endDate'          => $endDate
            ]);
            // $nearbyDuplicates->each->delete();
        }
    }
}
