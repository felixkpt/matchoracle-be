<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Game;
use App\Models\Team;
use App\Services\Client;
use App\Services\GameSources\Interfaces\MatchesInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class MatchesHandler implements MatchesInterface
{
    protected $is_fixtures = false;
    protected $has_errors = false;

    use ForebetInitializationTrait;
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

        $results = $this->prepareFetch($competition_id, $season_id);

        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else return $results;

        $uri = $source->source_uri . ($this->is_fixtures ? '/fixtures/' : '/results/') . $season_str;
        $url = $this->sourceUrl . ltrim($uri, '/');

        $links = [];
        if (!request()->shallow_fetch)
            $links = $this->getMatchesLinks($url);

        // if is not array then there could be an error that has occured
        if (!is_array($links)) return $links;

        $links = array_unique(array_merge([$uri], $links));

        $messages = [];
        $saved = $updated = 0;
        foreach ($links as $link) {
            $url = $this->sourceUrl . ltrim($link, '/');

            $content = Client::get($url);
            if (!$content) $this->has_errors = true;

            $crawler = new Crawler($content);
            [$saved_new, $updated_new, $msg_new] = $this->handleMatches($competition, $season, $crawler);
            $saved = $saved + $saved_new;
            $updated = $updated + $updated_new;
            $messages[] = $msg_new;

            sleep(5);
        }

        if (!$this->has_errors && $season && !$season->is_current && Carbon::parse($season->end_date)->isPast()) {
            $season->update(['fetched_all_matches' => true]);
        }

        $message = implode(', ', $messages);

        $response = ['message' => $message, 'results' => ['saved_updated' => $saved + $updated]];

        if (request()->without_response) return $response;

        return response($response);
    }

    private function getMatchesLinks($url)
    {
        $content = Client::get($url);
        if (!$content) return $this->matchMessage('Source not accessible or not found.');

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

        $msg = "";
        $saved = $updated = 0;
        try {

            DB::beginTransaction();

            $country = $competition->country;

            $date_not_found = [];
            $country_not_found = [];
            $competition_not_found = [];
            $home_team_not_found = [];
            $away_team_not_found = [];

            foreach ($matchesData as $key => $match) {

                if ($match['date']) {

                    $homeTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                        $q->where('source_uri', $match['home_team']['uri']);
                    })->first();
                    if (!$homeTeam) {
                        $homeTeam = (new TeamsHandler())->updateOrCreate($match['home_team'], $country, $competition, $season, true);
                    }

                    $awayTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                        $q->where('source_uri', $match['away_team']['uri']);
                    })->first();
                    if (!$awayTeam) {
                        $awayTeam = (new TeamsHandler())->updateOrCreate($match['away_team'], $country, $competition, $season, true);
                    }


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
                } else {
                    $no_date_mgs = ['competition' => $competition->id, 'season' => $season->id, 'match' => $match];
                    $date_not_found['match'][$key] = $match;
                    Log::critical('Match has no date:', $no_date_mgs);
                }
            }

            DB::commit();

            $msg = "Fetching matches completed, (saved $saved, updated: $updated).";

            if (count($date_not_found) > 0) {
                $msg .= ' ' . count($date_not_found) . ' dates were not found.';
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
        } catch (\Exception $e) {
            DB::rollBack();
            $this->has_errors = true;

            Log::error('Error during data import: ' . $e->getMessage() . ', File: ' . $e->getFile() . ', Line no:' . $e->getLine());
            $msg = 'Error during data import.';
        }

        return [$saved, $updated, $msg];
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
                    $k = $crawler->filter('td.resLresLTd a');
                    $gameUri = null;
                    if ($k->count() === 1) {
                        $gameUri = $k->attr('href');
                    }
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
                                $match['game_details']['full_time_results'] = null;
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
        // Extracting necessary information for creating or updating a game
        $competition_id = $competition->id;
        $season_id = $season->id;
        $country_id = $country->id;
        $date = Carbon::parse($match['date'] . ' ' . $match['time']);
        $utc_date = $date->format('Y-m-d H:i');

        // $season_end_date = Carbon::parse($season->end_date)->addYear();
        // // Assuming $season_end_date is a variable representing the end date of the season
        // if (Carbon::parse($utc_date)->greaterThan($season_end_date)) {
        //     // Log the critical error
        //     Log::critical('UTC date is newer than season end date. Aborting process.' . $utc_date . ' <--->' . $season_end_date);
        //     return false;
        // }

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
            'season_id' => $season_id,
            'country_id' => $country_id,
            'utc_date' => $utc_date,
            'has_time' => $has_time,
            'status' => $status,
            'matchday' => $matchday,
            'stage' => $stage,
            'group' => $group,
            'status_id' => $status_id,
            'user_id' => $user_id,
        ];

        // Check if a game with the same details already exists
        $game = Game::query()
            ->whereDate('utc_date', $date->format('Y-m-d'))
            ->where([
                ['competition_id', $competition_id],
                ['home_team_id', $homeTeam->id],
                ['away_team_id', $awayTeam->id],
                ['season_id', $season_id],
            ])->first();

        // If the game exists, update it; otherwise, create a new one
        if ($game) {
            $game->update($arr);
            $msg = 'updated';
        } else {
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

        // If it's not a fixture, store scores
        if ($game && !$this->is_fixtures) {
            $this->storeScores($game, $match['game_details']);
        }

        // Return a message indicating whether the game was saved or updated
        return $msg;
    }
}
