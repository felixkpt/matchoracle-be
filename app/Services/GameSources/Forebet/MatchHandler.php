<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;
use App\Models\Game;
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

        if ($game->results_status == 2) {
            return $this->matchMessage('Update status is satisfied.');
        }

        if ($game->last_fetch >= Carbon::now()->subHours(2)) {
            return $this->matchMessage('Last fetch is ' . (Carbon::parse($game->last_fetch)->diffForHumans()));
        }

        $gameSource = $game->gameSources()->where('game_source_id', $this->sourceId)->first();

        if (!$gameSource) {
            return $this->matchMessage('No game source uri');
        }

        $source_uri = $gameSource->pivot->source_uri;
        if (!$source_uri) {
            return $this->matchMessage('No source/details uri');
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

        $home_team_logo = null;
        if ($l->count() === 1)
            $home_team_logo = $l->attr('src');

        $l = $header->filter('time div.date_bah');
        if ($l->count() === 0) {
            $response = ['message' => 'Source has no date.'];
            if (request()->without_response) return $response;
            return response($response, 500);
        }

        $dt_raw = preg_replace('#\/#', '-', $l->text());

        $has_time = false;
        if (Str::endsWith($dt_raw, 'GMT')) {
            $date_time = Carbon::parse($dt_raw)->addMinutes(0)->format('Y-m-d H:i:s');
            $has_time = true;
        } else
            $date_time = Carbon::parse($dt_raw)->format('Y-m-d H:i:s');

        $l = $header->filter('div.weather_main_pr div span');
        $stadium = null;
        if ($l->count() === 1)
            $stadium = $l->text();

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

        $full_time_results = $half_time_results = null;

        $res = $crawler->filter('div#1x2_table .rcnt')->filter('.lscr_td')->first();

        if ($res->count() > 0) {
            $l = $res->filter('.lscrsp');
            $full_time_results = null;
            if ($l->count() === 1)
                $full_time_results = $l->text();

            $l = $res->filter('.ht_scr');
            $half_time_results = null;
            if ($l->count() === 1) {
                $half_time_results = $l->text();
                $half_time_results = preg_replace('#\)|\(#', '', $half_time_results);
            }
        }

        $one_x_two_odds = array_slice(array_filter($crawler->filter('div#1x2_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
            $odds = $node->text();
            if ($odds > 0 && $odds < 30)
                return $odds;
            else
                return;
        })), 0, 3);
        $over_under_odds = array_filter($crawler->filter('div#uo_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
            $odds = $node->text();
            if ($odds > 0 && $odds < 20)
                return $odds;
            else
                return;
        }));
        $gg_ng_odds = array_filter($crawler->filter('div#bts_table .rcnt')->filter('.prmod .haodd span')->each(function (Crawler $node) {
            $odds = $node->text();
            if ($odds > 0 && $odds < 20)
                return $odds;
            else
                return;
        }));

        $data = [
            'home_team_logo' => $home_team_logo,
            'utc_date' => $date_time,
            'has_time' => $has_time,
            'stadium' => $stadium,
            'competition' => $competition,
            'competition_url' => $competition_url,
            'away_team_logo' => $away_team_logo,
            'full_time_results' => $full_time_results,
            'half_time_results' => $half_time_results,
            'one_x_two_odds' => $one_x_two_odds,
            'over_under_odds' => $over_under_odds,
            'gg_ng_odds' => $gg_ng_odds,

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
            if ($data['full_time_results']) {
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
                $msg .= ' Time updated (' . $game_utc_date . ' > ' . $data['utc_date'].').';


            $game->update($arr);
            // update season fetched_all_single_matches
            if ($game->season->games()->where('results_status', 2)->count() === 0)
                $game->season->update(['fetched_all_single_matches' => true]);

            OddsHandler::updateOrCreate([
                'utc_date' => $data['utc_date'],
                'has_time' => $data['has_time'],
                'home_team' => $game['homeTeam']->name,
                'away_team' => $game['awayTeam']->name,
                'one_x_two_odds' => $data['one_x_two_odds'],
                'over_under_odds' => $data['over_under_odds'],
                'gg_ng_odds' => $data['gg_ng_odds'],
                'game_id' => $game->id,
                'source_id' => $this->sourceId,
                'competition' => $competition,
            ]);

            return $msg;
        } else {
            // delete fixture, date changed
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
