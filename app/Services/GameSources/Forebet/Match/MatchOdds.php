<?php

namespace App\Services\GameSources\Forebet\Match;

use App\Services\OddsHandler;
use Symfony\Component\DomCrawler\Crawler;

class MatchOdds
{

    public function oddsAndPredictions(Crawler $crawler, $tableSelector, $predictionSelector, $oddsSelector, $numPredictions, $maxOdds)
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

    public function oddsAndPredictionsForHDAFT(Crawler $crawler)
    {
        return $this->oddsAndPredictions(
            $crawler,
            'div#m1x2_table .rcnt',
            '.fprc span',
            '.prmod .haodd span',
            3,
            3
        );
    }

    public function oddsAndPredictionsForHDAHT(Crawler $crawler)
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

    public function oddsAndPredictionsForOverUnder(Crawler $crawler)
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

    public function oddsAndPredictionsForBTSTable(Crawler $crawler)
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

    public function oddsAndPredictionsForCS(Crawler $crawler)
    {
        $hda = $crawler->filter('div#m1x2_table .rcnt');

        $res = $hda->filter('.ex_sc.tabonly');
        $cs_pred = null;
        if ($res->count() > 0) {
            $cs_pred = $res->text();
        }

        return [null, $cs_pred, null];
    }

    public function saveOdds($sourceId, $game, $data, $competition)
    {
        OddsHandler::updateOrCreate([
            'utc_date' => $data['utc_date'],
            'has_time' => $data['has_time'],
            'home_team' => $game['homeTeam']->name,
            'away_team' => $game['awayTeam']->name,
            'hda_odds' => $data['ft_hda_odds'],
            'over_under_odds' => $data['over_under_odds'],
            'gg_ng_odds' => $data['gg_ng_odds'],
            'game_id' => $game->id,
            'source_id' => $sourceId,
            'competition' => $competition,
        ]);
    }
}
