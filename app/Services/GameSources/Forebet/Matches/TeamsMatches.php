<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\CompetitionAbbreviation;
use App\Models\Game;
use App\Services\Common;
use App\Services\GameSources\Forebet\ForebetInitializationTrait;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeamsMatches
{
    use ForebetInitializationTrait, MatchesTrait;

    public function fetchMatches($game, $competition, $crawler, $source_uri)
    {

        $html = $crawler->filter('table.stat-content tr td.floatLeft.statWidth div.moduletable div.st_scrblock');

        if ($html->count() > 0) {
            $country = $game->competition->country ?? null;
            $finalMessage = "";
            $totalSaved = $totalUpdated = 0;

            // Handle Head-to-Head Matches
            $headToHeadMatches = $html->eq(0)->filter('div.st_rmain div.st_row');
            if ($headToHeadMatches->count() > 0) {
                $matches = $this->getMatchesFromDOM($game, $competition, $country, $source_uri, $headToHeadMatches);
                if (count($matches) > 0) {
                    [$headToHeadSaved, $headToHeadUpdated, $headToHeadMessage] = $this->saveGames($matches);
                    $totalSaved += $headToHeadSaved;
                    $totalUpdated += $headToHeadUpdated;
                    $finalMessage .= $headToHeadMessage . " ---- ";
                }
            }

            // Handle Home Matches
            $homeTeamMatches = $html->eq(1)->filter('div.st_rmain div.st_row');
            if ($homeTeamMatches->count() > 0) {
                $matches = $this->getMatchesFromDOM($game, $competition, $country, $source_uri, $homeTeamMatches);
                if (count($matches) > 0) {
                    [$homeSaved, $homeUpdated, $homeMessage] = $this->saveGames($matches, null, null, true);
                    // $this->deactivateGames($game, $matches, 'home');
                    $totalSaved += $homeSaved;
                    $totalUpdated += $homeUpdated;
                    $finalMessage .= $homeMessage . " ---- ";
                }
            }

            // Handle Away Matches
            $awayTeamMatches = $html->eq(2)->filter('div.st_rmain div.st_row');
            if ($awayTeamMatches->count() > 0) {
                $matches = $this->getMatchesFromDOM($game, $competition, $country, $source_uri, $awayTeamMatches);
                if (count($matches) > 0) {
                    [$awaySaved, $awayUpdated, $awayMessage] = $this->saveGames($matches, null, null, true);
                    // $this->deactivateGames($game, $matches, 'away');
                    $totalSaved += $awaySaved;
                    $totalUpdated += $awayUpdated;
                    $finalMessage .= $awayMessage;
                }
            }

            // Combine totals and message
            return [$totalSaved, $totalUpdated, trim($finalMessage, " ---- ")];
        }
    }

    private function getMatchesFromDOM($game, $competition, $country, $source_uri, $crawler)
    {

        $chosen_crawler = $crawler;

        if (!$chosen_crawler) {
            return [];
        }

        // Now $chosen_crawler contains the desired crawler
        $matches = [];
        // Extracted data from the HTML will be stored in this array
        $matches = $chosen_crawler->each(function ($crawler) use ($game, $competition, $country, $source_uri) {

            // $this->updateCompetitionSourceID($competition, $crawler);

            $date = null;
            $raw_date = $crawler->filter('div.st_date div');

            $day_month = $raw_date->eq(0);
            $year = $raw_date->eq(1);
            if ($day_month->count() === 1 && $year->count() === 1) {
                try {
                    $day_month = implode('/', array_reverse(explode('/', trim($day_month->text()))));
                    $year = trim($year->text());
                    $date = Carbon::createFromFormat('Y/m/d', $year . '/' . $day_month, config('app.timezone'))->toDateString();
                } catch (InvalidFormatException $e) {
                    Log::error("Date parsing error for competition #{$competition->id}, source_uri: {$source_uri} : " . $e->getMessage());
                    $date = null;
                }
            }

            if (!$date) {
                return null;
            }

            $time = '00:00:00';
            $utc_date = $date . ' ' . $time;
            $match = [
                'date' => $date,
                'utc_date' => $utc_date,
                'time' => $time,
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
            $match['home_team']['name'] = trim($k->text());
            $match['home_team']['uri'] = getUriFromUrl($k->attr('href'));

            $k = $crawler->filter('div.st_ateam a');
            $match['away_team']['name'] = trim($k->text());
            $match['away_team']['uri'] = getUriFromUrl($k->attr('href'));


            [$full_time_results, $half_time_results, $uri] = $this->getScoresAndURI($game, $crawler);
            $match['game_details']['full_time_results'] = $full_time_results;
            $match['game_details']['half_time_results'] = $half_time_results;
            $match['game_details']['uri'] = $uri;

            // If match is historical (older than 5 days) and has no results, skip it
            $parsed_date = Carbon::parse($date);
            if (($parsed_date->isPast() && Carbon::now()->diffInDays($parsed_date) > 5) && $full_time_results === null) {
                Log::error("Historical game has no results, competition #{$competition->id}, source_uri: {$source_uri}, game_details URI: {$uri}");
                return null;
            }

            $k = $crawler->filter('div.st_ltag');
            if ($k->count()) {
                $abbrv = trim($k->text());

                $competition_abbrv = CompetitionAbbreviation::where('name', $abbrv)
                    ->when($country, function ($q) use ($country) {
                        $q->where('country_id', $country->id);
                    });

                if ($competition_abbrv->count() === 1) {
                    $competition_abbrv = $competition_abbrv->first();
                    $competition = $competition_abbrv->competition;

                    // Check if competition exists
                    if ($competition) {
                        $match['competition'] = $competition;
                        // Return only if competition exists
                        return $match;
                    }
                } elseif ($competition_abbrv->count() > 1) {
                    Log::warning("Multiple competition abbreviations found for: '{$abbrv}', country: {$country->name}. Manual review required.");
                } else {
                    Log::info("Missing competition abbreviation: '{$abbrv}', country: {$country->name}, match will not be saved");
                }
            }

            return null; // Return null for matches without competition
        });

        // Remove null values from the matches array
        $matches = array_values(array_filter($matches));

        return $matches;
    }

    private function updateCompetitionSourceID($competition, $crawler)
    {
        // Update competition source ID
        $competition_id = $competition->id ?? null;

        if ($competition_id) {
            // Retrieve the source ID based on the crawler
            $source_id = $this->getCompetionSourceId($crawler);

            // Attempt to retrieve the pivot data from the relationship
            $pivot = $competition->gameSources()
                ->where(function ($query) use ($competition_id) {
                    $query->where('game_source_id', $this->sourceId)
                        ->where('competition_id', $competition_id);
                })
                ->first()
                ->pivot ?? null;

            if (!$pivot->source_id) {
                if ($pivot) {
                    // Access or update the source_id from the pivot
                    $pivot->source_id = $source_id;
                    $pivot->save();
                } else {
                    // Handle the case where the pivot data is not found
                    Log::warning("Pivot data not found for Competition ID: $competition_id and Game Source ID: {$this->sourceId}");
                }
            }
        }
    }

    private function getCompetionSourceId($crawler)
    {
        $classes = $crawler->attr('class'); // Get the class attribute
        preg_match('/\bstlg_(\d+)\b/', $classes, $matches); // Capture the digits after "stlg_"
        $source_competion_id = isset($matches[1]) ? (int) $matches[1] : null;
        return $source_competion_id;
    }

    private function getScoresAndURI($game, $crawler)
    {
        $full_time_results = $half_time_results = $uri = null;

        $k = $crawler->filter('div.st_rescnt');

        if ($k->count()) {
            $k = $crawler->filter('div.st_rescnt a.stat_link');
            if ($k->count()) {
                $uri = $k->attr('href');
            }

            $k = $crawler->filter('span.st_res');
            if ($k->count()) {
                $full_time_results = trim($k->text());
            }

            $k = $crawler->filter('span.st_htscr');
            if ($k->count()) {
                $half_time_results = Str::before(Str::after(trim($k->text()), '('), ')');
            }
        } else {
            $game_id = $game->id;
            Log::info("TeamsMatches error getting scores for games found while crawling game #{$game_id}:");
        }

        return  [$full_time_results, $half_time_results, $uri];
    }
}
