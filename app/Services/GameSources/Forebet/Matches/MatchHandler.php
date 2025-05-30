<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\Game;
use App\Services\ClientHelper\Client;
use App\Services\Common;
use App\Services\GameSources\Forebet\ForebetInitializationTrait;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class MatchHandler
{

    use ForebetInitializationTrait;

    protected $has_errors = false;
    protected $matchOdds;
    protected $teamsMatches;
    protected $sourcePreds;
    /**
     * Constructor for the CompetitionsHandler class.
     * 
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();
        $this->matchOdds = new MatchOdds();
        $this->teamsMatches = new TeamsMatches();
        $this->sourcePreds = new SourcePreds();
    }

    function fetchMatch($game_id)
    {
        $game = Game::query()->where('id', $game_id)->firstOrFail();

        if (!request()->ignore_results && !request()->is_odds_request && !in_array($game->game_score_status_id, unsettledGameScoreStatuses())) {
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


        return $this->handleGame($game, $source_uri);
    }

    private function handleGame($game, $source_uri)
    {

        $url = $this->sourceUrl . ltrim($source_uri, '/');

        $content = Client::get($url);
        if (!$content) return $this->matchMessage('Source inaccessible or not found.', 500);

        // $content = file_get_contents(Storage::path('tests/index.html'));
        
        $crawler = new Crawler($content);

        if (strpos($crawler->text(), 'Attention Required!') !== false) {
            $message = "Attention Required! Blocked while getting game #{$game->id}";
            Log::channel($this->logChannel)->critical($message);
            return $this->matchMessage($message, 500);
        }

        $header = $crawler->filter('div.predictioncontain');
        $l = $header->filter('div.lLogo a img.matchTLogo');

        $res = $header->filter('.weather_main_pr');
        $temperature = null;
        if ($res->count() > 0)
            $temperature = $this->getTemperature($res);


        $wc = $header->filter('.weather_main_pr img.wthc');
        $weather_condition = null;
        if ($wc->count() === 1)
            $weather_condition = $wc->attr('src');

        $competition_node = $crawler->filter('center.leagpredlnk a');
        $competition_url = null;
        $competition = null;
        if ($competition_node->count() === 1) {
            $competition_url = $competition_node->attr('href');
            $competition = $competition_node->text();
        }

        $l = $header->filter('div.rLogo a img.matchTLogo');
        $away_team_logo = null;
        if ($l->count() === 1)
            $away_team_logo = $l->attr('src');

        $postponed = $cancelled = false;

        $full_time_results = $half_time_results = null;

        $res = $crawler->filter('div#m1x2_table .rcnt')->filter('.lscr_td')->first();

        if ($res->count() > 0) {
            $l = $res->filter('.lscrsp');
            $full_time_results = null;
            if ($l->count() === 1) {
                $full_time_results = $l->text();
            } else {
                // Case game was Postp.
                $res = $crawler->filter('div#m1x2_table .rcnt')->filter('.lmin_td .l_min')->first();
                if ($res->count() > 0) {
                    $postponed = $res->text() == 'Postp.';
                    $cancelled = $res->text() == 'Cancl.';
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

        $home_team_logo = $this->getTeamLogo($header, 'div.lLogo a img.matchTLogo');
        $utc_date = $this->parseDateTime($header);
        $has_time = $this->hasTime($header);
        $stadium = $this->getStadium($header);

        // Initialize default values for the variables before the try-catch block
        $ft_hda_odds = $ft_hda_preds = $ft_hda_preds_pick = null;
        $over_under_odds = $over_under_preds = $over_under_preds_pick = null;
        $gg_ng_odds = $gg_ng_preds = $gg_ng_preds_pick = null;
        $cs_odds = $cs_pred = $cs_pred_pick = null;
        $ht_hda_odds = $ht_hda_preds = $ht_hda_preds_pick = null;

        try {
            // Attempt to fetch the odds and predictions from the match crawler
            [$ft_hda_odds, $ft_hda_preds, $ft_hda_preds_pick] = $this->matchOdds->oddsAndPredictionsForHDAFT($crawler);
            [$over_under_odds, $over_under_preds, $over_under_preds_pick] = $this->matchOdds->oddsAndPredictionsForOverUnder($crawler);
            [$gg_ng_odds, $gg_ng_preds, $gg_ng_preds_pick] = $this->matchOdds->oddsAndPredictionsForBTSTable($crawler);
            [$cs_odds, $cs_pred, $cs_pred_pick] = $this->matchOdds->oddsAndPredictionsForCS($crawler);
            [$ht_hda_odds, $ht_hda_preds, $ht_hda_preds_pick] = $this->matchOdds->oddsAndPredictionsForHDAHT($crawler);
        } catch (Exception $e) {
            // Log any errors that occur while fetching the odds and predictions
            Log::channel($this->logChannel)->critical('MatchHandler, Odds error: ' . $e->getMessage());
        }

        // Convert the predictions to numeric or appropriate values
        $ft_hda_preds_pick = ($ft_hda_preds_pick == '1' ? 0 : ($ft_hda_preds_pick == 'X' ? 1 : ($ft_hda_preds_pick == '2' ? 2 : null)));
        $over_under_preds_pick = ($over_under_preds_pick == 'Under') ? 0 : ($over_under_preds_pick == 'Over' ? 1 : null);
        $gg_ng_preds_pick = ($gg_ng_preds_pick == 'No') ? 0 : ($gg_ng_preds_pick == 'Yes' ? 1 : null);
        $ht_hda_preds_pick = ($ht_hda_preds_pick == '1' ? 0 : ($ht_hda_preds_pick == 'X' ? 1 : ($ht_hda_preds_pick == '2' ? 2 : null)));

        if (!empty($game['competition_id']))
            $competition = $game->competition;
        else
            $competition = Common::saveCompetition($competition_url, $competition);

        $data = [
            'home_team_logo' => $home_team_logo,
            'utc_date' => $utc_date,
            'has_time' => $has_time,
            'stadium' => $stadium,

            'away_team_logo' => $away_team_logo,
            'full_time_results' => $full_time_results,
            'half_time_results' => $half_time_results,
            'postponed' => $postponed,
            'cancelled' => $cancelled,

            'temperature' => $temperature,
            'weather_condition' => $weather_condition,
        ];


        $message = $this->teamsMatches->updateGame($game, $data);

        $oddsAndPredsData = [
            'utc_date' => $utc_date,
            'has_time' => $has_time,
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

        ];

        $this->sourcePreds->savePreds($this->sourceId, $game->id, $oddsAndPredsData);
        $this->matchOdds->saveOdds($this->sourceId, $game, $oddsAndPredsData, $competition);

        $saved = 0;
        $updated = $message ? 1 : 0;

        // AOB taking advantage of matches on page
        $handled_teams_games = [];
        if (!request()->is_odds_request){
            $handled_teams_games = $this->teamsMatches->fetchMatches($game, $competition, $crawler, $source_uri);
        }

        $response = [
            'message' => $message,
            'status' => $saved > 0 ? 200 : 201,
            'results' => ['created_counts' => $saved, 'updated_counts' => $updated,  'failed_counts' => 0, 'handled_teams_games' => $handled_teams_games]
        ];

        if (request()->without_response) return $response;

        return response($response);
    }

    private function getTemperature($res)
    {
        $temperatureElements = explode(', ', $res->text());

        $temperature = null;
        if (count($temperatureElements) == 1) {
            $temperatureElements = end($temperatureElements);
            // Extract the single temperature if it's not a range
            preg_match('/(\d+)°/', $temperatureElements, $matches);
            if (isset($matches[1])) {
                $temperatures = [$matches[1]];
            }
        } elseif (count($temperatureElements) > 1) {
            $temperatureElements = end($temperatureElements);

            $temperatures = [];

            // Check if the temperature element contains a temperature range
            if (strpos($temperatureElements, ' - ') !== false) {
                // Match both temperatures in the range
                preg_match_all('/(\d+)°/', $temperatureElements, $matches);
                if (count($matches[1]) >= 2) {
                    $temperatures = $matches[1];
                }
            } else {
                // Extract the single temperature if it's not a range
                preg_match('/(\d+)°/', $temperatureElements, $matches);
                if (isset($matches[1])) {
                    $temperatures = [$matches[1]];
                }
            }

            // Convert temperatures to integers and join with " - " if it's a range
            if (!empty($temperatures)) {
                $temperature = implode(' - ', array_map('intval', $temperatures));
            }
        }

        return $temperature;
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
}
