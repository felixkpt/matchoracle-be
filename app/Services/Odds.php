<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Odds
{
    static function save($data)
    {

        if (count($data['one_x_two']) !== 3)
            return false;

        $table = Carbon::parse($data['date_time'])->format('Y') . '_odds';
        self::createTable($table);

        $odds = autoModel($table);

        try {
            DB::beginTransaction();

            $odds->updateOrCreate(['home_team' => $data['home_team'], 'away_team' => $data['away_team'], 'date' => $data['date'], 'game_id' => $data['game_id'] ?? null], [
                'date_time' => $data['date_time'],
                'year' => $data['year'],
                'date' => $data['date'],
                'time' => $data['time'],
                'has_time' => $data['has_time'],
                'home_team' => $data['home_team'],
                'away_team' => $data['away_team'],
                'home_win_odds' => $data['one_x_two'][0],
                'draw_odds' => $data['one_x_two'][1],
                'away_win_odds' => $data['one_x_two'][2],
                'over_odds' => $data['over_under'][0] ?? null,
                'under_odds' => $data['over_under'][1] ?? null,
                'gg_odds' => $data['gg_ng'][0] ?? null,
                'ng_odds' => $data['gg_ng'][1] ?? null,
                'game_id' => $data['game_id'] ?? null,
                'competition_id' => $data['competition_id'] ?? null,
                'source' => $data['source'] ?? null,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Odds save failed:', ['err' => $e->getMessage(), 'data' => $data]);
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
                $table->dateTime('date_time');
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