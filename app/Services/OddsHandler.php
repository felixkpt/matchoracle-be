<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Odd;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OddsHandler
{
    static function updateOrCreate($data)
    {

        if (count($data['hda_odds']) !== 3)
            return false;

        try {
            DB::beginTransaction();

            $res = Odd::updateOrCreate(
                [
                    'home_team' => $data['home_team'], 'away_team' => $data['away_team'],
                    'utc_date' => $data['utc_date'], 'game_id' => $data['game_id'] ?? null
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

            if (isset($data['game_id'])) {
                Game::find($data['game_id'])->odds()->sync($res);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Odds save failed:', ['err' => $e->getMessage(), 'data' => $data]);
        }

        if (isset($data['competition']) && $data['competition']) {
            $competition = $data['competition'];
            $predictionCount = Odd::whereHas('game', fn ($qry) => $qry->where('competition_id', $competition->id))->count();
            $competition->update([
                'is_odds_enabled' => true,
                'odds_counts' => $predictionCount
            ]);
            $competition->save();
        }
    }

    static function whereGame($game)
    {

        $table = str_replace('games', 'odds', $game['table']);
        return autoModel($table)->where('game_id', $game['id']);
    }


    private static function createTable($table)
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->dateTime('utc_date');
                $table->year('year');
                $table->date('date');
                $table->time('time')->nullable();
                $table->boolean('has_time')->default(0);
                $table->string('home_team')->nullable();
                $table->string('away_team')->nullable();
                $table->uuid('competition_id')->nullable();
                $table->string('source')->nullable();
                $table->decimal('home_win_odds', 6, 2, true)->nullable();
                $table->decimal('draw_odds', 6, 2, true)->nullable();
                $table->decimal('away_win_odds', 6, 2, true)->nullable();
                $table->decimal('over_odds', 6, 2, true)->nullable();
                $table->decimal('under_odds', 6, 2, true)->nullable();
                $table->decimal('gg_odds', 6, 2, true)->nullable();
                $table->decimal('ng_odds', 6, 2, true)->nullable();
                $table->uuid('game_id')->nullable();
                $table->uuid('user_id');
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });
        }
    }
}
