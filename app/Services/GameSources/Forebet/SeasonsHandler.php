<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;
use App\Models\Season;
use App\Services\Client;
use stdClass;
use Symfony\Component\DomCrawler\Crawler;

class SeasonsHandler extends BaseHandlerController
{

    function fetchSeasons($competition_id, $season = null)
    {

        $competition = Competition::whereHas('gameSources', function ($q) use ($competition_id) {
            $q->where('competition_id', $competition_id);
        })->first();

        if (!$competition) {
            return response(['message' => 'Competition #' . $competition_id . ' not found.'], 404);
        }

        // Access the source_id value for the pivot
        $source = $competition->gameSources()->where('game_source_id', $this->sourceId)->first();
        if (!$source) {
            return response(['message' => 'Source for competition #' . $competition_id . ' not found.'], 404);
        }

        $source = $source->pivot;

        if (!$source->is_subscribed) {
            return response(['message' => 'Source #' . $source->source_id . ' not subscribed.'], 402);
        }

        $url = $this->sourceUrl . ltrim($source->source_uri, '/');
        echo ($url) . "\n";

        $content = Client::get($url);
        $crawler = new Crawler($content);

        // Extracted data from the HTML will be stored in this array
        $seasons = [];
        $crawler->filter('.contentmiddle .category_nav select#season option')->each(function ($crawler) use (&$seasons) {
            if ($crawler->count() > 0) {
                $text = explode('/', $crawler->text());
                $startDate = $text[0] . '-01-01';
                $endDate = $text[1] . '-01-01';
                $season = new stdClass();
                $season->startDate = $startDate;
                $season->endDate = $endDate;
                $seasons[] = $season;
            }
        });


        $country = $competition->country;

        $seasonsData = $seasons;

        foreach ($seasonsData as $key => $seasonData) {
            $is_current = ($key == 0);

            if ($seasonData->startDate) {
                (new self())::updateOrCreate($seasonData, $country, $competition, $is_current);
            }
        }

        return response(['message' => 'Seasons for ' . $competition->name . ' updated.']);
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
