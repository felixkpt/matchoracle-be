<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\Game;
use App\Models\Season;
use App\Services\ClientHelper\Client;
use App\Services\GameSources\Forebet\ForebetInitializationTrait;
use App\Services\GameSources\Interfaces\MatchesInterface;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class MatchesHandler implements MatchesInterface
{
    protected $is_fixtures = false;
    protected $has_errors = false;

    use ForebetInitializationTrait, MatchesTrait;
    /**
     * Constructor for the CompetitionsHandler class.
     * 
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();
    }

    function fetchMatches($competition_id, $season_id = null, $is_fixtures = false)
    {

        $this->is_fixtures = $is_fixtures;

        // if its fixtures and season is not active return error message

        if ($is_fixtures && $season_id) {
            $is_current = !!Season::findOrFail($season_id)->is_current;
            // if is not array then there could be an error that has occured
            if (!$is_current) return $this->matchMessage('When fetching fixtures season should be active.', 422);
        }

        $results = $this->prepareFetch($competition_id, $season_id);

        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else return $results;

        $uri = $source->source_uri . ($this->is_fixtures ? '/fixtures/' : '/results/') . $season_str;
        $url = $this->sourceUrl . ltrim($uri, '/');

        $links = ['data' => []];
        if (!request()->shallow_fetch) {
            $links = $this->getMatchesLinks($url, $competition);
        }

        // if is not array then there could be an error that has occured
        if (!isset($links['data'])) return $links;

        $links = array_unique(array_merge([$uri], $links['data']));

        $messages = [];
        $saved = $updated = 0;
        foreach ($links as $i => $link) {
            $url = $this->sourceUrl . ltrim($link, '/');

            $content = Client::get($url);
            if (!$content) $this->has_errors = true;

            $crawler = new Crawler($content);

            // Season integrity test
            $season_start_end = null;
            if ($season) {
                $season_start_end = Str::before($season->start_date, '-') . '-' . Str::before($season->end_date, '-');

                // Remove query parameters and hash
                $test_url = strtok($url, '?');
                $test_url = strtok($test_url, '#');
                if (!Str::endsWith($test_url, $season_start_end)) {
                    Log::channel($this->logChannel)->critical('Season miss-match: #' . $competition->id . ', #' . $season->id);
                    $season = null;
                }
            }


            [$saved_new, $updated_new, $msg_new] = $this->handleMatches($competition, $season, $crawler);
            $saved = $saved + $saved_new;
            $updated = $updated + $updated_new;
            $messages[] = $msg_new;

            sleep(5);
        }

        if ($saved > 0) {
            // update the matches counts
            $gameCount = Game::where('competition_id', $competition->id)->count();
            $competition->update([
                'games_counts' => $gameCount,
            ]);
        }

        if (!$this->has_errors && $season && !$season->is_current && Carbon::parse($season->end_date)->isPast()) {
            $season->update(['fetched_all_matches' => true]);
        }

        $message = implode(', ', $messages);

        $response = ['message' => $message, 'results' => ['created_counts' => $saved, 'updated_counts' => $updated,  'failed_counts' => 0], 'status' => 200];

        if (request()->without_response) return $response;

        return response($response);
    }

    private function getMatchesLinks($url, $competition)
    {
        $content = Client::get($url);
        if (!$content) return $this->matchMessage('Source not accessible or not found.', 504);

        $crawler = new Crawler($content);

        if (strpos($crawler->text(), 'Attention Required!') !== false) {
            $message = "Attention Required! Blocked while getting matches for compe #{$competition->id}";
            Log::channel($this->logChannel)->critical($message);
            return $this->matchMessage($message, 500);
        }

        // list-footer
        // Get all links inside the .list-footer element
        $links = $crawler->filter('.contentmiddle .list-footer a')->each(function ($crawler) {
            return $crawler->attr('href');
        });
        $links = array_values(array_filter(array_unique($links)));

        return ['data' => $links];
    }

    private function handleMatches($competition, $season, $crawler)
    {

        $matchesData = $this->is_fixtures ? $this->filterUpcomingMatches($competition, $season, $crawler) : $this->filterPlayedMatches($competition, $season, $crawler);

        $msg = "";
        $saved = $updated = 0;

        if (!is_array($matchesData)) {
            abort(500, "Cannot get matches for: compe#$competition->id, season#$season->id");
        }

        [$saved, $updated, $msg] = $this->saveGames($matchesData, $competition, $season);

        return [$saved, $updated, $msg];
    }

    private function filterPlayedMatches($competition, $season, $crawler)
    {
        $matches = [];

        try {

            $chosen_crawler = null;
            $has_matches = false;
            $crawler->filter('.contentmiddle table[border="0"]')->each(function ($crawler) use (&$chosen_crawler, &$has_matches) {
                $has_matches = true;
                if ($crawler->filter('tr.heading')->count() > 0) {
                    $chosen_crawler = $crawler;
                    return false;
                }
            });

            if (!$chosen_crawler) {
                Log::critical('MatchesHandler Error: No chosen_crawler!! Competition #' . $competition->id . ', season #' . $season->id);
                return $has_matches ? [] : null;
            };

            // Now $chosen_crawler contains the desired crawler
            $matches = [];
            $date = null;


            // Extracted data from the HTML will be stored in this array
            $matches = $chosen_crawler->filter('tr')->each(function ($crawler, $index) use (&$date) {

                if ($crawler->count() > 0) {
                    $heading = $crawler->filter('.heading');
                    if ($heading->count() > 0) {
                        $raw_date = $heading->filter('td b')->text();

                        if ($raw_date && $raw_date != $date) {
                            $date = Carbon::parse($raw_date)->setTimezone('UTC')->format('Y-m-d');
                        }
                    } else if ($date) {

                        $time = $crawler->filter('td.resLdateTd')->text();
                        $homeTeam = $crawler->filter('td.resLnameRTd a')->text();
                        $homeTeamUri = $crawler->filter('td.resLnameRTd a')->attr('href');
                        $gameResults = $crawler->filter('td.resLresLTd')->text();

                        $k = $crawler->filter('td.resLresLTd a');
                        $gameUri = null;
                        if ($k->count() === 1) {
                            $gameUri = $k->attr('href');
                        }
                        $awayTeam = $crawler->filter('td.resLnameLTd a')->text();
                        $awayTeamUri = $crawler->filter('td.resLnameLTd a')->attr('href');

                        $utc_date = $date . ' ' . $time;

                        $match = [
                            'date' => $date,
                            'utc_date' => $utc_date,
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
        } catch (Exception $e) {
            Log::channel($this->logChannel)->critical("FetchMatches > filterPlayedMatches Error : " . $e->getMessage());
        }

        return $matches;
    }

    private function filterUpcomingMatches($competition, $season, $crawler)
    {
        $matches = [];

        try {

            $chosen_crawler = null;
            $has_matches = false;
            $crawler->filter('.contentmiddle table[border="0"]')->each(function ($crawler) use (&$chosen_crawler, &$has_matches) {
                $has_matches = true;
                if ($crawler->filter('tr.heading')->count() > 0) {
                    $chosen_crawler = $crawler;
                    return false;
                }
            });

            if (!$chosen_crawler) return $has_matches ? [] : null;

            // Now $chosen_crawler contains the desired crawler
            $date = null;
            // Extracted data from the HTML will be stored in this array
            $matches = $chosen_crawler->filter('tr')->each(function ($crawler) use (&$date) {

                if ($crawler->count() > 0) {
                    $heading = $crawler->filter('.heading');
                    if ($heading->count() > 0) {
                        $raw_date = $heading->filter('td b')->text();

                        if ($raw_date && $raw_date != $date) {
                            $date = Carbon::parse($raw_date)->setTimezone('UTC')->format('Y-m-d');
                        }
                    } else if ($date && Carbon::parse($date)->setTimezone('UTC')->isFuture()) {

                        $time = '00:00:00';
                        $utc_date = $date . ' ' . $time;

                        $match = [
                            'date' => $date,
                            'utc_date' => $utc_date,
                            'time' => $time,
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

                        $this->addMatchDetails($crawler, $match);

                        return $match;
                    }
                }
            });

            $matches = array_values(array_filter($matches));
        } catch (Exception $e) {
            Log::channel($this->logChannel)->critical("FetchMatches > filterUpcomingMatches Error : " . $e->getMessage());
        }

        return $matches;
    }

    function addMatchDetails($crawler, &$match)
    {
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
                    $match['game_details']['full_time_results'] = null;
                    $match['game_details']['uri'] = getUriFromUrl($k->attr('href'));
                }
            }
        });
    }
}
