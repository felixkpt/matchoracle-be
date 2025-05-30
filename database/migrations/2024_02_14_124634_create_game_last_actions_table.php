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
        Schema::create('game_last_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('game_id')->unique();
                        
            $table->dateTime('match_recent_results_last_fetch')->nullable();
            $table->dateTime('match_historical_results_last_fetch')->nullable();
            $table->dateTime('match_fixtures_last_fetch')->nullable();
            $table->dateTime('match_shallow_fixtures_last_fetch')->nullable();

            $table->dateTime('odd_recent_results_last_fetch')->nullable();
            $table->dateTime('odd_historical_results_last_fetch')->nullable();
            $table->dateTime('odd_fixtures_last_fetch')->nullable();
            $table->dateTime('odd_shallow_fixtures_last_fetch')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_last_actions');
    }
};
