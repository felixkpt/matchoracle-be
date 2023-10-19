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
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('competition_id');
            $table->uuid('home_team_id');
            $table->uuid('away_team_id');
            $table->uuid('season_id');
            $table->uuid('country_id');
            $table->datetime('utc_date');
            $table->string('status');
            $table->integer('matchday')->nullable();
            $table->string('stage');
            $table->string('group')->nullable();
            $table->uuid('game_score_id')->nullable();
            $table->dateTime('last_updated')->nullable();
            $table->dateTime('last_fetch')->nullable();
            $table->unsignedInteger('priority_number')->default(9999);
            $table->uuid('status_id')->default(0);
            $table->uuid('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
