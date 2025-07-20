<?php

namespace App\Services\GameSources\Forebet\Matches;

use App\Models\GameSourcePrediction;

class SourcePreds
{

    function savePreds($sourceId, $game_id, $data)
    {

        GameSourcePrediction::updateOrCreate(
            [
                'source_id' => $sourceId,
                'utc_date' => $data['utc_date'],
                'game_id' => $game_id,
            ],
            [
                'source_id' => $sourceId,
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
}
