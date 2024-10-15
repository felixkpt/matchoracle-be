<?php

namespace App\Services\GameSources\Forebet\Match;

use App\Models\Competition;
use App\Models\CompetitionAbbreviation;
use App\Models\Game;
use App\Services\Common;
use App\Services\GameSources\Forebet\ForebetInitializationTrait;
use App\Services\OddsHandler;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TeamsMatches
{
    use ForebetInitializationTrait;

    function fetchMatches($game, $crawler)
    {
        $html = $crawler->filter('table.stat-content tr td.floatLeft.statWidth div.moduletable div.st_scrblock');

        if ($html->count() > 0) {

            $firstHtml = $html->eq(1); // Get the first element

            $home_team_matches = $firstHtml->filter('div.st_rmain div.st_row');

            $secondHtml = $html->eq(2); // Get the second element
            $away_team_matches = $secondHtml->filter('div.st_rmain div.st_row');

            $country = $game->competition->country ?? null;

            $msg = "";
            $saved = $updated = 0;
            $msg2 = "";
            $saved2 = $updated2 = 0;
            if ($home_team_matches->count() > 0) {
                $matches = $this->filterMatches($home_team_matches, $country);
                if (count($matches) > 0) {
                    [$saved, $updated, $msg] = $this->saveGames($matches);
                    $this->deactivateGames($game, $matches, 'home');
                }
            }

            if ($away_team_matches->count() > 0) {
                $matches = $this->filterMatches($away_team_matches, $country);
                if (count($matches) > 0) {
                    [$saved2, $updated2, $msg2] = $this->saveGames($matches);
                    $this->deactivateGames($game, $matches, 'away');
                }
            }

            $savedTotal = $saved + $saved2;
            $updatedTotal = $updated + $updated2;
            $msg = "Fetching matches completed, (saved $savedTotal, updated: $updatedTotal).";

            [$saved2, $updated2, $msg];
        }
    }

    private function filterMatches($crawler, $country)
    {
        $chosen_crawler = $crawler;

        if (!$chosen_crawler) return [];

        // Now $chosen_crawler contains the desired crawler
        $matches = [];
        // Extracted data from the HTML will be stored in this array
        $matches = $chosen_crawler->each(function ($crawler) use ($country) {

            $date = null;
            $raw_date = $crawler->filter('div.st_date div');
            $day_month = $raw_date->eq(0);
            $year = $raw_date->eq(1);
            if ($day_month->count() === 1 && $year->count() === 1) {
                $day_month = implode('/', array_reverse(explode('/', $day_month->text())));
                $year = $year->text();
                $date = Carbon::parse($year . '/' . $day_month)->toDateString();
            }

            $match = [
                'date' => $date,
                'time' => '00:00:00',
                'has_time' => false,
                'home_team' => [
                    'id' => null,
                    'name' => null,
                    'uri' => null,
                ],
                'game_details' => [
                    'full_time_results' => null,
                    'half_time_results' => null,
                    'uri' => null,
                ],
                'away_team' => [
                    'id' => null,
                    'name' => null,
                    'uri' => null,
                ],
                'competition' => null,
            ];

            $k = $crawler->filter('div.st_hteam a');
            $match['home_team']['name'] = $k->text();
            $match['home_team']['uri'] = getUriFromUrl($k->attr('href'));

            $k = $crawler->filter('div.st_ateam a');
            $match['away_team']['name'] = $k->text();
            $match['away_team']['uri'] = getUriFromUrl($k->attr('href'));

            $k = $crawler->filter('div.st_rescnt a');
            if ($k->count()) {

                $k = $crawler->filter('span.st_res');
                if ($k->count()) {
                    $match['game_details']['full_time_results'] = $k->text();
                }

                $k = $crawler->filter('span.st_htscr');
                if ($k->count()) {
                    $match['game_details']['half_time_results'] = Str::before(Str::after($k->text(), '('), ')');
                }

                $match['game_details']['uri'] = getUriFromUrl($k->attr('href'));
            }

            $k = $crawler->filter('div.st_ltag');
            if ($k->count()) {
                $abbrv = $k->text();

                $competition_abbrv = CompetitionAbbreviation::where('name', $abbrv)->when($country, function ($q) use ($country) {
                    $q->where('country_id', $country->id);
                });

                if ($competition_abbrv->count() === 1) {
                    $competition_abbrv = $competition_abbrv->first();
                    $competition = Competition::find($competition_abbrv->competition_id);
                    // Check if competition exists
                    if ($competition) {
                        $match['competition'] = $competition;
                        // Return only if competition exists
                        return $match;
                    }
                }
            }

            return null; // Return null for matches without competition
        });

        // Remove null values from the matches array
        $matches = array_values(array_filter($matches));

        return $matches;
    }

    public function updateGame($game, $data)
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
            if ($data['full_time_results'] || $data['postponed']) {
                $scores = $data;
                $results_status = $this->storeScores($game, $scores);
            }

            if ($stadium)
                $arr['stadium_id'] = $stadium->id;

            if ($weather_condition)
                $arr['weather_condition_id'] = $weather_condition->id;

            // add abbr if not exists
            $this->handleCompetitionAbbreviation($competition);

            $msg = 'Game updated successfully, (results status ' . ($results_status > -1 ? ($game_results_status . ' > ' . $results_status) : 'unchanged') . ').';

            if (Carbon::parse($data['utc_date'])->isFuture()) {
                $msg = 'Fixture updated successfully.';
            }

            if ($game_utc_date != $data['utc_date'])
                $msg .= ' Time updated (' . $game_utc_date . ' > ' . $data['utc_date'] . ').';


            $game->update($arr);
            // update season fetched_all_single_matches
            if ($game->season && $game->season->games()->whereIn('game_score_status_id', unsettledGameScoreStatuses())->count() === 0) {
                $game->season->update(['fetched_all_single_matches' => true]);
            }



            return $msg;
        } else {
            // delete fixture, date changed
        }
    }


    function deactivateGames($game, $matches, $playing = 'home')
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
}
