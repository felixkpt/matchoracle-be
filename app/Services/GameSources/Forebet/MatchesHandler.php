<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;
use App\Models\Country;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Referee;
use App\Models\Season;
use App\Models\Team;
use App\Services\Client;
use App\Services\GameSources\Interfaces\MatchesInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;


class MatchesHandler extends BaseHandlerController implements MatchesInterface
{
    protected $is_fixtures = false;

    function fetchMatches($competition_id, $season_id = null, $is_fixtures = false)
    {
        $this->is_fixtures = $is_fixtures;

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
        $source = $competition->gameSources()->where(function ($q) use ($competition_id) {
            $q->where('game_source_id', $this->sourceId)->where('competition_id', $competition_id);
        })->first()->pivot;

        if (!$source) {
            return response(['message' => 'Source for competition #' . $competition_id . ' not found.'], 404);
        }

        if (!$source->is_subscribed) {
            return response(['message' => 'Source #' . $source->source_id . ' not subscribed.'], 402);
        }


        $uri = ltrim($source->source_uri . ($this->is_fixtures ? '/fixtures/' : '/results/') . $season_str, '/');
        $url = $this->sourceUrl . $uri;
        echo ($url) . "\n<br>";

        $links = $this->getMatchesLinks($url);

        $links = array_unique(array_merge([$uri], $links));

        $results = [];
        foreach ($links as $link) {
            $url = $this->sourceUrl . ltrim($link, '/');
            echo ($url) . "\n<br>";

            $content = Client::get($url);
            $crawler = new Crawler($content);
            $results[] = $this->handleMatches($competition, $season, $crawler);
            sleep(2);
        }


        return response(['message' => $results]);
    }

    private function getMatchesLinks($url)
    {
        $content = Client::get($url);
        $crawler = new Crawler($content);

        // list-footer
        // Get all links inside the .list-footer element
        $links = $crawler->filter('.contentmiddle .list-footer a')->each(function ($crawler) {
            return $crawler->attr('href');
        });
        $links = array_values(array_filter(array_unique($links)));
        return $links;
    }

    private function handleMatches($competition, $season, $crawler)
    {

        $matchesData = $this->is_fixtures ? $this->filterUpcomingMatches($crawler) : $this->filterPlayedMatches($crawler);

        try {

            DB::beginTransaction();

            $country = $competition->country;

            $country_not_found = [];
            $competition_not_found = [];
            $home_team_not_found = [];
            $away_team_not_found = [];
            $saved = 0;
            $updated = 0;

            foreach ($matchesData as $match) {

                $homeTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                    $q->where('source_uri', $match['home_team']['uri']);
                })->first();

                $awayTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                    $q->where('source_uri', $match['away_team']['uri']);
                })->first();


                if ($homeTeam && $awayTeam) {
                    // All is set can now save game!
                    $result = $this->saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam);

                    // Check the result of the save operation
                    if ($result === 'saved') {
                        $saved++;
                    } elseif ($result === 'updated') {
                        $updated++;
                    }
                } else {

                    if (!$homeTeam) {

                        if (!isset($home_team_not_found[$country->name])) {
                            $home_team_not_found[$match['home_team']['name']] = 1;
                        } else {
                            $home_team_not_found[$match['home_team']['name']] = $home_team_not_found[$match['home_team']['name']] + 1;
                        }

                        Log::critical('homeTeam not found:', (array) $match['home_team']['name']);
                    }

                    if (!$awayTeam) {

                        if (!isset($away_team_not_found[$country->name])) {
                            $away_team_not_found[$match['away_team']['name']] = 1;
                        } else {
                            $away_team_not_found[$match['away_team']['name']] = $away_team_not_found[$match['away_team']['name']] + 1;
                        }

                        Log::critical('awayTeam not found:', (array) $match['away_team']['name']);
                    }
                }
            }

            DB::commit();

            $msg = "Fetching matches completed, (saved $saved, updated: $updated).";

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

            return $msg;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error during data import: ' . $e->getMessage() . ', File: ' . $e->getFile() . ', Line no:' . $e->getLine(),);
            return 'Error during data import.';
        }
    }

    private function filterPlayedMatches($crawler)
    {
        $chosen_crawler = null;
        $crawler->filter('.contentmiddle table[border="0"]')->each(function ($crawler) use (&$chosen_crawler) {
            if ($crawler->filter('tr.heading')->count() > 0) {
                $chosen_crawler = $crawler;
                return false;
            }
        });

        if (!$chosen_crawler) return null;

        // Now $chosen_crawler contains the desired crawler
        $matches = [];
        $date = null;
        // Extracted data from the HTML will be stored in this array
        $matches = $chosen_crawler->filter('tr')->each(function ($crawler) use (&$date) {

            if ($crawler->count() > 0) {
                $heading = $crawler->filter('.heading');
                if ($heading->count() > 0) {
                    $raw_date = $heading->filter('td b')->text();

                    if ($raw_date && $raw_date != $date) {
                        $date = Carbon::parse($raw_date)->format('Y-m-d');
                    }
                } else if ($date) {

                    $time = $crawler->filter('td.resLdateTd')->text();
                    $homeTeam = $crawler->filter('td.resLnameRTd a')->text();
                    $homeTeamUri = $crawler->filter('td.resLnameRTd a')->attr('href');
                    $gameResults = $crawler->filter('td.resLresLTd')->text();
                    $gameUri = $crawler->filter('td.resLresLTd a')->attr('href');
                    $awayTeam = $crawler->filter('td.resLnameLTd a')->text();
                    $awayTeamUri = $crawler->filter('td.resLnameLTd a')->attr('href');

                    $match = [
                        'date' => $date,
                        'time' => $time,
                        'has_time' => !!$time,
                        'home_team' => [
                            'name' => $homeTeam,
                            'uri' => $homeTeamUri,
                        ],
                        'game_details' => [
                            'full_time_results' => $gameResults,
                            'uri' => $gameUri,
                        ],
                        'away_team' => [
                            'name' => $awayTeam,
                            'uri' => $awayTeamUri,
                        ],
                    ];

                    return $match;
                }
            }
        });

        $matches = array_values(array_filter($matches));

        return $matches;
    }

    private function filterUpcomingMatches($crawler)
    {
        $chosen_crawler = null;
        $crawler->filter('.contentmiddle table[border="0"]')->each(function ($crawler) use (&$chosen_crawler) {
            if ($crawler->filter('tr.heading')->count() > 0) {
                $chosen_crawler = $crawler;
                return false;
            }
        });

        if (!$chosen_crawler) return null;

        // Now $chosen_crawler contains the desired crawler
        $matches = [];
        $date = null;
        // Extracted data from the HTML will be stored in this array
        $matches = $chosen_crawler->filter('tr')->each(function ($crawler) use (&$date) {

            if ($crawler->count() > 0) {
                $heading = $crawler->filter('.heading');
                if ($heading->count() > 0) {
                    $raw_date = $heading->filter('td b')->text();

                    if ($raw_date && $raw_date != $date) {
                        $date = Carbon::parse($raw_date)->format('Y-m-d');
                    }
                } else if ($date) {


                    $match = [
                        'date' => $date,
                        'time' => '00:00:00',
                        'has_time' => false,
                        'home_team' => [
                            'name' => null,
                            'uri' => null,
                        ],
                        'game_details' => [
                            'full_time_results' => null,
                            'uri' => null,
                        ],
                        'away_team' => [
                            'name' => null,
                            'uri' => null,
                        ],
                    ];

                    $crawler->filter('td')->each(function ($crawler, $i) use (&$match) {

                        if ($i === 1) {
                            $k = $crawler->filter('a');
                            $match['home_team']['name'] = $k->text();
                            $match['home_team']['uri'] = getUriFromUrl($k->attr('href'));
                        } elseif ($i === 2) {
                        } elseif ($i === 3) {
                            $k = $crawler->filter('a');
                            $match['away_team']['name'] = $k->text();
                            $match['away_team']['uri'] = getUriFromUrl($k->attr('href'));
                        } elseif ($i === 4) {
                            $k = $crawler->filter('a');
                            if ($k->count()) {
                                $match['game_details']['full_time_results'] = $k->text();
                                $match['game_details']['uri'] = getUriFromUrl($k->attr('href'));
                            }
                        }
                    });

                    return $match;
                }
            }
        });

        $matches = array_values(array_filter($matches));

        return $matches;
    }

    private function saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam)
    {

        $competition_id = $competition->id;
        $season_id = $season->id;
        $country_id = $country->id;
        $date = Carbon::parse($match['date'] . ' ' . $match['time']);
        $utc_date = Carbon::parse($match['date'] . ' ' . $match['time'])->toDateTimeString();
        $has_time = $match['has_time'];
        $status = $date->isFuture() ? 'SCHEDULED' : (preg_match('#:#', $match['date']) ? 'FINISHED' : 'PENDING');
        $matchday = null;
        $stage = null;
        $group = null;
        $last_updated = null;
        $last_fetch = Carbon::now();
        $status_id = activeStatusId();
        $user_id = auth()->id();

        $commonArr = [
            'competition_id' => $competition_id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'season_id' => $season_id,
            'country_id' => $country_id,
            'utc_date' => $utc_date,
            'has_time' => $has_time,
        ];

        $game = Game::updateOrCreate(
            $commonArr,
            array_merge(
                $commonArr,
                [
                    'status' => $status,
                    'matchday' => $matchday,
                    'stage' => $stage,
                    'group' => $group,
                    'last_updated' => $last_updated,
                    'last_fetch' => $last_fetch,
                    'status_id' => $status_id,
                    'user_id' => $user_id,
                ]
            )
        );

        $msg = null;
        if ($game) {

            if (!$this->is_fixtures)
                $this->storeScores($game, $match['game_details']);

            // sync referees
            $this->syncReferees($game, $match);

            if ($game->wasRecentlyCreated) {
                $msg = 'saved';
            } else {
                $msg = 'updated';
            }
        }

        return $msg;
    }

    private function storeScores($game, $score)
    {

        $game_details_uri = $score['uri'];
        $game->gameSources()->attach($this->sourceId, ['source_uri' => $game_details_uri]);

        $full_time_results = $score['full_time_results'];

        if (Str::contains($full_time_results, ':')) return false;

        $winner = null;
        $home_scores_full_time = null;
        $away_scores_full_time = null;

        if (Str::contains($full_time_results, '-')) {
            $arr = explode('-', $full_time_results);
            $home_scores_full_time = $arr[0];
            $away_scores_full_time = $arr[1];

            if ($home_scores_full_time > $away_scores_full_time)
                $winner = 'HOME_TEAM';
            elseif ($home_scores_full_time == $away_scores_full_time)
                $winner = 'DRAW';
            elseif ($home_scores_full_time < $away_scores_full_time)
                $winner = 'AWAY_TEAM';
        }


        $arr = [
            'game_id' => $game->id,
            'winner' => $winner,
            'duration' => null,

            'home_scores_full_time' => $home_scores_full_time,
            'away_scores_full_time' => $away_scores_full_time,
        ];

        if (isset($score['full_time_results'])) {
        }

        GameScore::updateOrCreate(
            [
                'game_id' => $game->id
            ],
            $arr
        );
    }

    private function syncReferees($game, $match)
    {

        if (isset($match->referees)) {

            $referees = $match->referees;
            $refsArr = [];
            foreach ($referees as $referee) {

                $country = Country::where('name', $referee->nationality)->first();

                $ref = Referee::updateOrCreate([
                    'name' => $referee->name,
                    'type' => $referee->type,
                    'country_id' => $country->id ?? 0,
                ]);

                if ($ref) {
                    $refsArr[] = $ref->id;
                }
            }

            $game->referees()->sync($refsArr);
        }
    }
}
