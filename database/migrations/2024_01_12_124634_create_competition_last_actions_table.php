<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competition_last_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('competition_id');
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->dateTime('abbreviations_last_fetch')->nullable();
            $table->dateTime('seasons_last_fetch')->nullable();

            $table->dateTime('standings_recent_results_last_fetch')->nullable();
            $table->dateTime('standings_historical_results_last_fetch')->nullable();

            $table->dateTime('matches_recent_results_last_fetch')->nullable();
            $table->dateTime('matches_historical_results_last_fetch')->nullable();
            $table->dateTime('matches_fixtures_last_fetch')->nullable();
            $table->dateTime('matches_shallow_fixtures_last_fetch')->nullable();

            $table->dateTime('match_recent_results_last_fetch')->nullable();
            $table->dateTime('match_historical_results_last_fetch')->nullable();
            $table->dateTime('match_fixtures_last_fetch')->nullable();
            $table->dateTime('match_shallow_fixtures_last_fetch')->nullable();

            $table->dateTime('odd_recent_results_last_fetch')->nullable();
            $table->dateTime('odd_historical_results_last_fetch')->nullable();
            $table->dateTime('odd_fixtures_last_fetch')->nullable();
            $table->dateTime('odd_shallow_fixtures_last_fetch')->nullable();

            $table->dateTime('predictions_last_train')->nullable();
            $table->date('predictions_trained_to')->nullable();

            $table->dateTime('predictions_last_done')->nullable();

            $table->dateTime('stats_last_done')->nullable();
            $table->dateTime('predictions_stats_last_done')->nullable();

            $table->timestamps();

            $table->unique(['competition_id', 'season_id'], 'competition_season_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_last_actions');
    }
};
