<?php

namespace App\Services\GameSources\Forebet;

use App\Models\CompetitionAbbreviation;
use App\Services\ClientHelper\Client;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\DomCrawler\Crawler;

trait CompetitionAbbreviationsTrait
{

    /**
     * Check if an abbreviation for the competition exists.
     *
     * @param Competition $competition The competition object.
     * @return bool True if the abbreviation exists, false otherwise.
     */
    private function abbreviationExists($competition)
    {
        return CompetitionAbbreviation::where('competition_id', $competition->id)->exists();
    }

    /**
     * Get the tag from the provided URL.
     *
     * @param string $url The URL to extract the tag from.
     * @return string|null The extracted tag or null if not found.
     */
    private function getTagFromUrl($url)
    {

        $timeToLive = 60 * 24 * 30;

        $content = $this->fetchWithCacheV2(
            $url,
            "abbreviations_html",       // Cache path
            $timeToLive,                // TTL minutes
            'local',                    // Storage disk
            $this->logChannel           // Optional log channel
        );

        if (!$content) {
            return null;
        }

        $crawler = new Crawler($content);

        $row = $crawler->filter('table.main tr td.contentmiddle div.rcnt div.stcn span.shortTag')->first();

        if ($row->count() === 1) {
            return trim($row->text());
        }

        return null;
    }

    /**
     * Create competition abbreviation if it doesn't exist.
     *
     * @param Competition $competition The competition object.
     * @param string $url The URL to extract the abbreviation tag from.
     * @return void
     */
    private function createCompetitionAbbreviation($competition, $url)
    {
        $message = '';
        $saved = $updated = 0;

        if (!$this->abbreviationExists($competition)) {
            $country = $competition->country;
            $tag = $this->getTagFromUrl($url);

            $res = null;
            if ($tag) {
                if ($country) {
                    $res = $this->createAbbreviation($tag, $competition, $country);
                } else {
                    $exists = CompetitionAbbreviation::where('name', $tag)->exists();
                    if (!$exists) {
                        $res = $this->createAbbreviation($tag, $competition, null);
                    }
                }
            }

            if ($res) {
                if ($res->wasRecentlyCreated) {
                    $saved = 1;
                    $message = 'Abbreviation created';
                } else {
                    $updated = 1;
                    $message = 'Abbreviation updated';
                }
            }
        }

        $response = ['message' => $message, 'results' => ['created_counts' => $saved, 'updated_counts' => $updated,  'failed_counts' => 0], 'status' => 200];

        if (request()->without_response) return $response;

        return response($response);
    }

    /**
     * Create a competition abbreviation record.
     *
     * @param string $tag The abbreviation tag.
     * @param Competition $competition The competition object.
     * @param Country $country The country object.
     * @return Model
     */
    private function createAbbreviation($tag, $competition, $country = null)
    {
        return CompetitionAbbreviation::updateOrCreate(
            [
                'name' => $tag,
                'country_id' => $country->id ?? null,
                'competition_id' => $competition->id,
            ],
            [
                'name' => $tag,
                'country_id' => $country->id ?? null,
                'competition_id' => $competition->id,
            ]
        );
    }
}
