<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Season;
use App\Services\ClientHelper\Client;
use App\Services\Common;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;
use Symfony\Component\DomCrawler\Crawler;

class SeasonsHandler
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

        if (!$this->jobId) {
            $this->jobId = str()->random(6);
        }
    }

    function fetchSeasons($competition_id)
    {

        $results = $this->prepareFetch($competition_id);

        if (is_array($results) && $results['message'] === true) {
            [$competition, $season, $source, $season_str] = $results['data'];
        } else return $results;

        $cachePath = "seasons_html/{$competition->id}.html";
        
        $shouldFetch = false;
        if (Storage::disk('local')->exists($cachePath)) {
            $lastModified = Carbon::createFromTimestamp(Storage::disk('local')->lastModified($cachePath));
            if ($lastModified->diffInMinutes(now()) < 10) {
                $content = Storage::disk('local')->get($cachePath);
                Log::channel($this->logChannel)->info("Reusing cached HTML for compe #{$competition->id}");
            } else {
                // expired, fetch fresh
                $shouldFetch = true;
            }
        } else {
            // no cache, fetch fresh
            $shouldFetch = true;
        }

        if ($shouldFetch) {
            $url = $this->sourceUrl . ltrim($source->source_uri, '/');
            // Fetch HTML content from the URL
            $content = Client::get($url);
            if (!$content) return $this->matchMessage('Source inaccessible');
            Storage::disk('local')->put($cachePath, $content);
        }

        $crawler = new Crawler($content);

        Log::channel($this->logChannel)->info("Getting seasons for compe #{$competition->id} ...");
        if (strpos($crawler->text(), 'Attention Required!') !== false) {
            $message = "Attention Required! Blocked while getting seasons for compe #{$competition->id}";
            Log::channel($this->logChannel)->critical($message);
            return $this->matchMessage($message, 500);
        }

        // Extracted data from the HTML will be stored in this array
        $seasons = [];
        $crawler->filter('.contentmiddle .category_nav select#season option')->each(function ($crawler) use (&$seasons) {
            if ($crawler->count() === 1) {
                $text = explode('/', $crawler->text());
                $startDate = $text[0] . '-01-01';
                $endDate = $text[1] . '-01-01';
                $season = new stdClass();
                $season->startDate = $startDate;
                $season->endDate = $endDate;
                $seasons[] = $season;
            }
        });


        if (count($seasons) === 0) {
            Log::channel($this->logChannel)->info("Getting seasons for compe #{$competition->id} using option 2 ...");

            $crawler->filter('select.league_year_select option')->each(function ($crawler) use (&$seasons, $competition) {
                if ($crawler->count() === 1) {
                    $text = explode('/', $crawler->text());
                    $startDate = $text[0] . '-01-01';
                    $endDate = $text[1] . '-01-01';
                    $season = new stdClass();
                    $season->startDate = $startDate;
                    $season->endDate = $endDate;
                    $seasons[] = $season;
                }
            });
        }

        if (!$competition->logo || !@file_get_contents($competition->logo)) {

            $elem = $crawler->filter('.contentmiddle h1.frontH img[alt="league_logo"]');
            if ($elem->count() == 1) {
                $img_src = $elem->attr('src');
                $path = Common::saveCompetitionLogo($img_src, $competition);

                if ($path) {
                    $competition->logo = $path;
                    $competition->save();
                }
            }
        }

        $country = $competition->country;

        $seasonsData = $seasons;

        $saved = $updated = 0;
        foreach ($seasonsData as $key => $seasonData) {
            $is_current = ($key == 0);

            if ($seasonData->startDate) {
                $season = (new self())::updateOrCreate($seasonData, $country, $competition, $is_current);

                if ($season->wasRecentlyCreated) {
                    $saved++;
                } else {
                    $updated++;
                }
            }
        }

        $message = 'Seasons for ' . $competition->name . ' saved/updated. ';
        $message .= $saved . ' saved, ' . $updated . ' updated.';

        $response = [
            'message' => $message,
            'status' => $saved > 0 ? 200 : 201,
            'results' => ['created_counts' => $saved, 'updated_counts' => $updated,  'failed_counts' => 0]
        ];

        if (request()->without_response) return $response;

        return response($response);
    }

    static function updateOrCreate($seasonData, $country, $competition, $is_current = false, $played_matches = null)
    {

        $arr = [
            'competition_id' => $competition->id,
            'start_date' => $seasonData->startDate,
            'end_date' => $seasonData->endDate,
            'is_current' => $is_current
        ];

        if (isset($seasonData->currentMatchday) && $seasonData->currentMatchday) {
            $arr['current_matchday'] = $seasonData->currentMatchday;
        }

        $winner = null;
        if (isset($seasonData->winner)) {
            $winner = app(TeamsHandler::class)->updateOrCreate($seasonData->winner, $country, $competition);
        }

        if ($winner) {
            $arr['winner_id'] = $seasonData->winner_id;
        }

        if ($played_matches) {
            $arr['played_matches'] = $played_matches;
        }

        $season = Season::updateOrCreate(
            [
                'competition_id' => $competition->id,
                'start_date' => $seasonData->startDate,
                'end_date' => $seasonData->endDate,
            ],
            $arr
        );

        return $season;
    }
}
