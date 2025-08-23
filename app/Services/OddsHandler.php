<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Odd;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OddsHandler
{
    public static function updateOrCreate($data)
    {


        if (count($data['hda_odds']) !== 3) {
            return false;
        }

        try {
            DB::beginTransaction();

            $odd = Odd::updateOrCreate(
                [
                    'home_team' => $data['home_team'],
                    'away_team' => $data['away_team'],
                    'utc_date' => $data['utc_date'],
                    'game_id' => $data['game_id'] ?? null
                ],
                [
                    'utc_date' => $data['utc_date'],
                    'has_time' => $data['has_time'],
                    'home_team' => $data['home_team'],
                    'away_team' => $data['away_team'],
                    'home_win_odds' => $data['hda_odds'][0],
                    'draw_odds' => $data['hda_odds'][1],
                    'away_win_odds' => $data['hda_odds'][2],

                    'under_25_odds' => $data['over_under_odds'][0] ?? null,
                    'over_25_odds' => $data['over_under_odds'][1] ?? null,

                    'gg_odds' => $data['gg_ng_odds'][0] ?? null,
                    'ng_odds' => $data['gg_ng_odds'][1] ?? null,

                    'game_id' => $data['game_id'] ?? null,
                    'source_id' => $data['source_id'] ?? null,
                ]
            );

            // Associate the Odd with the Game if a game_id is provided
            if (!empty($data['game_id'])) {
                $game = Game::find($data['game_id']);
                if ($game) {
                    // Check if the odd is already linked to the game
                    if (!$game->odds()->where('odds.id', $odd->id)->exists()) {
                        $game->odds()->save($odd); // Save only if not already associated
                    }
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Odds save failed:', ['err' => $e->getMessage(), 'data' => $data]);
        }

        if (isset($data['competition']) && $data['competition']) {
            $competition = $data['competition'];
            $predictionCount = Odd::whereHas('game', fn($qry) => $qry->where('competition_id', $competition->id))->count();
            $competition->update([
                'is_odds_enabled' => true,
                'odds_counts' => $predictionCount
            ]);
            $competition->save();
        }
    }
}
