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
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('home_team_id');
            $table->unsignedBigInteger('away_team_id');
            $table->unsignedBigInteger('season_id')->nullable();
            $table->unsignedBigInteger('country_id');
            $table->date('date');
            $table->datetime('utc_date');
            $table->boolean('has_time')->default(false);
            $table->string('status');
            $table->integer('matchday')->nullable();
            $table->string('stage')->nullable();
            $table->string('group')->nullable();
            $table->integer('game_score_status_id')->default(0);
            $table->unsignedInteger('priority_number')->default(9999);
            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();

            // Adding the unique index to restrain duplicates
            $table->unique(['home_team_id', 'away_team_id', 'date'], 'unique_game_match');
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
