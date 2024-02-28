<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Game;
use App\Models\GameSourcePrediction;
use App\Services\Client;
use App\Services\Common;
use App\Services\OddsHandler;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;


class MatchHandler
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
    }

    function fetchMatch($game_id)
    {
        $game = Game::query()
            ->where('status_id', activeStatusId())
            ->where('id', $game_id)
            ->firstOrFail();

        Log::alert('$game', [$game]);
        
        if ($game->results_status == 2) {
            return $this->matchMessage('Update status is satisfied.');
        }

        $gameSource = $game->gameSources()->where('game_source_id', $this->sourceId)->first();

        if (!$gameSource) {
            return $this->matchMessage('No game source uri');
        }

        $source_uri = $gameSource->pivot->source_uri;
        if (!$source_uri) {
            return $this->matchMessage('No source/details uri');
        }

        if ($game->last_fetch >= Carbon::now()->subHours(2)) {
            return $this->matchMessage('Last fetch is ' . (Carbon::parse($game->last_fetch)->diffForHumans()));
        }

        $url = $this->sourceUrl . ltrim($source_uri, '/');

        return $this->handleGame($game, $url);
    }

    private function handleGame($game, $url)
    {
        $content = Client::get($url);
        if (!$content) return $this->matchMessage('Source inaccessible or not found.', 500);

        $crawler = new Crawler($content);

        $header = $crawler->filter('div.predictioncontain');
        $l = $header->filter('div.lLogo a img.matchTLogo');

        $temperatureElement = explode(', ', $header->filter('.weather_main_pr')->text());

        $temperature = null;
        if (count($temperatureElement) > 1) {
            $temperatureElement = end($temperatureElement);

            $temperatures = [];
            // Check if the temperature element contains a temperature range
            if (strpos($temperatureElement, ' - ') !== false) {
                preg_match_all('/(\d+)°/', $temperatureElement, $matches);
                if (count($matches[1]) >= 2) {
                    $temperatures = $matches[1];
                }
            } else {
                // Extract the single temperature from the element
                preg_match('/(\d+)°/', $temperatureElement, $matches);
                if (count($matches) > 1) {
                    $temperatures = [$matches[1]];
                }
            }

            if ($temperatures)
                $temperature = implode(' - ', array_map('intval', $temperatures));
        }

        $wc = $header->filter('.weather_main_pr img.wthc');
        $weather_condition = null;
        if ($wc->count() === 1)
            $weather_condition = $wc->attr('src');

        $competition = $crawler->filter('center.leagpredlnk a');
        $competition_url = $competition->attr('href');
        $competition = $competition->text();

        $l = $header->filter('div.rLogo a img.matchTLogo');
        $away_team_logo = null;
        if ($l->count() === 1)
            $away_team_logo = $l->attr('src');

        $postponed = false;
        $full_time_results = $half_time_results = null;

        $res = $crawler->filter('div#1x2_table .rcnt')->filter('.lscr_td')->first();

        if ($res->count() > 0) {
            $l = $res->filter('.lscrsp');
            $full_time_results = null;
            if ($l->count() === 1) {
                $full_time_results = $l->text();
            } else {
                // Case game was Postp.
                $res = $crawler->filter('div#1x2_table .rcnt')->filter('.lmin_td .l_min')->first();
                if ($res->count() > 0) {
                    $postponed = $res->text() == 'Postp.';
                }
            }

            $l = $res->filter('.ht_scr');
            $half_time_results = null;
            if ($l->count() === 1) {
                $half_time_results = $l->text();
                $half_time_results = preg_replace('#\)|\(#', '', $half_time_results);
            }
        }


        $header = $crawler->filter('div.predictioncontain');

        [$ft_hda_odds, $ft_hda_preds, $ft_hda_preds_pick] = $this->oddsAndPredictionsForHDAFT($crawler);
        [$over_under_odds, $over_under_preds, $over_under_preds_pick] = $this->oddsAndPredictionsForOverUnder($crawler);
        [$gg_ng_odds, $gg_ng_preds, $gg_ng_preds_pick] = $this->oddsAndPredictionsForBTSTable($crawler);
        [$cs_odds, $cs_pred, $cs_pred_pick] = $this->oddsAndPredictionsForCS($crawler);
        [$ht_hda_odds, $ht_hda_preds, $ht_hda_preds_pick] = $this->oddsAndPredictionsForHDAHT($crawler);

        $ft_hda_preds_pick = ($ft_hda_preds_pick == '1' ? 0 : ($ft_hda_preds_pick == 'X' ? 1 : ($ft_hda_preds_pick == '2' ? 2 : null)));
        $over_under_preds_pick = ($over_under_preds_pick == 'Under') ? 0 : ($over_under_preds_pick == 'Over' ? 1 : null);
        $gg_ng_preds_pick = ($gg_ng_preds_pick == 'No') ? 0 : ($gg_ng_preds_pick == 'Yes' ? 1 : null);

        $ht_hda_preds_pick = ($ht_hda_preds_pick == '1' ? 0 : ($ht_hda_preds_pick == 'X' ? 1 : ($ht_hda_preds_pick == '2' ? 2 : null)));

        $data = [
            'home_team_logo' => $this->getTeamLogo($header, 'div.lLogo a img.matchTLogo'),
            'utc_date' => $this->parseDateTime($header),
            'has_time' => $this->hasTime($header),
            'stadium' => $this->getStadium($header),

            'competition' => $competition,
            'competition_url' => $competition_url,
            'away_team_logo' => $away_team_logo,
            'full_time_results' => $full_time_results,
            'half_time_results' => $half_time_results,
            'postponed' => $postponed,

            'ft_hda_odds' => $ft_hda_odds,
            'ft_hda_preds' => $ft_hda_preds,
            'ft_hda_preds_pick' => $ft_hda_preds_pick,

            'over_under_odds' => $over_under_odds,
            'over_under_preds' => $over_under_preds,
            'over_under_preds_pick' => $over_under_preds_pick,

            'gg_ng_odds' => $gg_ng_odds,
            'gg_ng_preds' => $gg_ng_preds,
            'gg_ng_preds_pick' => $gg_ng_preds_pick,

            'cs_pred' => $cs_pred,
            'cs_odds' => $cs_odds,

            'ht_hda_odds' => $ht_hda_odds,
            'ht_hda_preds' => $ht_hda_preds,
            'ht_hda_preds_pick' => $ht_hda_preds_pick,

            'temperature' => $temperature,
            'weather_condition' => $weather_condition,
        ];

        $message = $this->updateGame($game, $data);
        $saved = 0;
        $updated = 1;

        $response = ['message' => $message, 'results' => ['saved_updated' => $saved + $updated]];

        if (request()->without_response) return $response;

        return response($response);
    }

    private function oddsAndPredictions(Crawler $crawler, $tableSelector, $predictionSelector, $oddsSelector, $numPredictions, $maxOdds)
    {
        $table = $crawler->filter($tableSelector);

        $predictions = array_slice(array_filter($table->filter($predictionSelector)->each(function (Crawler $node) {
            $pred = $node->text();
            return ($pred > 0 && $pred <= 100) ? $pred : null;
        })), 0, $numPredictions);

        $pick = $table->filter('.forepr')->text();

        $odds = array_slice(array_filter($table->filter($oddsSelector)->each(function (Crawler $node) {
            $odd = $node->text();
            return ($odd > 0 && $odd < 30) ? $odd : null;
        })), 0, $maxOdds);

        return [
            $odds,
            $predictions,
            $pick,
        ];
    }

    private function oddsAndPredictionsForHDAFT(Crawler $crawler)
    {
        return $this->oddsAndPredictions(
            $crawler,
            'div#1x2_table .rcnt',
            '.fprc span',
            '.prmod .haodd span',
            3,
            3
        );
    }

    private function oddsAndPredictionsForHDAHT(Crawler $crawler)
    {
        return $this->oddsAndPredictions(
            $crawler,
            'div#htft_table .rcnt',
            '.fprc span',
            '.prmod .haodd span',
            3,
            3
        );
    }

    private function oddsAndPredictionsForOverUnder(Crawler $crawler)
    {
        return $this->oddsAndPredictions(
            $crawler,
            'div#uo_table .rcnt',
            '.fprc span',
            '.prmod .haodd span',
            2,
            2
        );
    }

    private function oddsAndPredictionsForBTSTable(Crawler $crawler)
    {
        return $this->oddsAndPredictions(
            $crawler,
            'div#bts_table .rcnt',
            '.fprc span',
            '.prmod .haodd span',
            2,
            2
        );
    }

    private function oddsAndPredictionsForCS(Crawler $crawler)
    {
        $hda = $crawler->filter('div#1x2_table .rcnt');

        $res = $hda->filter('.ex_sc.tabonly');
        $cs_pred = null;
        if ($res->count() > 0) {
            $cs_pred = $res->text();
        }

        return [null, $cs_pred, null];
    }

    private function getTeamLogo($header, $selector)
    {
        $logoElement = $header->filter($selector);
        return $logoElement->count() === 1 ? $logoElement->attr('src') : null;
    }

    private function parseDateTime($header)
    {
        $dateElement = $header->filter('time div.date_bah');
        if ($dateElement->count() === 0) {
            $this->handleNoDate();
        }

        $dtRaw = preg_replace('#\/#', '-', $dateElement->text());

        return Str::endsWith($dtRaw, 'GMT')
            ? Carbon::parse($dtRaw)->addMinutes(0)->format('Y-m-d H:i')
            : Carbon::parse($dtRaw)->format('Y-m-d H:i');
    }

    private function hasTime($header)
    {
        $dateElement = $header->filter('time div.date_bah');
        return Str::endsWith(preg_replace('#\/#', '-', $dateElement->text()), 'GMT');
    }

    private function getStadium($header)
    {
        $stadiumElement = $header->filter('div.weather_main_pr div span');
        return $stadiumElement->count() === 1 ? $stadiumElement->text() : null;
    }

    private function handleNoDate()
    {
        $response = ['message' => 'Source has no date.'];
        if (request()->without_response) {
            return $response;
        }

        return response($response, 500);
    }

    private function updateGame($game, $data)
    {

        Common::saveTeamLogo($game['homeTeam'], $data['home_team_logo']);
        Common::saveTeamLogo($game['awayTeam'], $data['away_team_logo']);

        $stadium = Common::saveStadium($data['stadium']);
        $weather_condition = Common::saveWeatherCondition($data['weather_condition']);

        if ($game['competition_id'])
            $competition = $game->competition;
        else
            $competition = Common::saveCompetition($data['competition_url'], $data['competition']);

        if ($game) {
            $game_utc_date = $game->utc_date;
            $game_results_status = $game->results_status;

            // common columns during create and update
            $arr = [
                'utc_date' => $data['utc_date'],
                'has_time' => $data['has_time'],
                'temperature' => $data['temperature'],
                'last_fetch' => now(),
            ];

            $results_status = -1;
            if ($data['full_time_results'] || $data['postponed']) {
                $scores = $data;
                $results_status = $this->storeScores($game, $scores);
            }

            if ($stadium)
                $arr['stadium_id'] = $stadium->id;

            if ($weather_condition)
                $arr['weather_condition_id'] = $weather_condition->id;

            $this->handleCompetitionAbbreviation($competition);

            $msg = 'Game updated successfully, (results status ' . ($results_status > -1 ? ($game_results_status . ' > ' . $results_status) : 'unchanged') . ').';

            if (Carbon::parse($data['utc_date'])->isFuture()) {
                $msg = 'Fixture updated successfully.';
            }

            if ($game_utc_date != $data['utc_date'])
                $msg .= ' Time updated (' . $game_utc_date . ' > ' . $data['utc_date'] . ').';


            $game->update($arr);
            // update season fetched_all_single_matches
            if ($game->season->games()->where('results_status', 2)->count() === 0)
                $game->season->update(['fetched_all_single_matches' => true]);

            OddsHandler::updateOrCreate([
                'utc_date' => $data['utc_date'],
                'has_time' => $data['has_time'],
                'home_team' => $game['homeTeam']->name,
                'away_team' => $game['awayTeam']->name,
                'hda_odds' => $data['ft_hda_odds'],
                'over_under_odds' => $data['over_under_odds'],
                'gg_ng_odds' => $data['gg_ng_odds'],
                'game_id' => $game->id,
                'source_id' => $this->sourceId,
                'competition' => $competition,
            ]);

            $this->saveSourcePreds($game->id, $data);

            return $msg;
        } else {
            // delete fixture, date changed
        }
    }

    function saveSourcePreds($game_id, $data)
    {

        GameSourcePrediction::updateOrCreate(
            [
                'source_id' => $this->sourceId,
                'utc_date' => $data['utc_date'],
                'game_id' => $game_id,
            ],
            [
                'source_id' => $this->sourceId,
                'utc_date' => $data['utc_date'],
                'game_id' => $game_id,

                // Full Time Predictions
                'ft_hda_pick' => $data['ft_hda_preds_pick'],
                'ft_home_win_proba' => $data['ft_hda_preds'][0] ?? null,
                'ft_draw_proba' => $data['ft_hda_preds'][1] ?? null,
                'ft_away_win_proba' => $data['ft_hda_preds'][2] ?? null,

                // Half Time Predictions
                'ht_hda_pick' => $data['ht_hda_preds_pick'],
                'ht_home_win_proba' => $data['ht_hda_preds'][0] ?? null,
                'ht_draw_proba' => $data['ht_hda_preds'][1] ?? null,
                'ht_away_win_proba' => $data['ht_hda_preds'][2] ?? null,

                // Both Teams to Score
                'bts_pick' => $data['gg_ng_preds_pick'],
                'ng_proba' => $data['gg_ng_preds'][0] ?? null,
                'gg_proba' => $data['gg_ng_preds'][1] ?? null,

                // Over/Under 2.5 Goals
                'over_under25_pick' => $data['over_under_preds_pick'],
                'under25_proba' => $data['over_under_preds'][0] ?? null,
                'over25_proba' => $data['over_under_preds'][1] ?? null,

                // Correct Score
                'cs' => scores()[$data['cs_pred']] ?? null,
            ]
        );
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
