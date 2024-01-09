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
        Schema::create('competition_statistics', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('season_id')->nullable();

            $table->date('date')->nullable();
            $table->integer('matchday')->nullable();

            $table->unsignedBigInteger('counts');
            
            $table->unsignedBigInteger('half_time_home_wins');
            $table->unsignedBigInteger('half_time_draws');
            $table->unsignedBigInteger('half_time_away_wins');
            $table->unsignedBigInteger('full_time_home_wins');
            $table->unsignedBigInteger('full_time_draws');
            $table->unsignedBigInteger('full_time_away_wins');
            $table->unsignedBigInteger('gg');
            $table->unsignedBigInteger('ng');
            $table->unsignedBigInteger('over15');
            $table->unsignedBigInteger('under15');
            $table->unsignedBigInteger('over25');
            $table->unsignedBigInteger('under25');
            $table->unsignedBigInteger('over35');
            $table->unsignedBigInteger('under35');

            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_statistics');
    }
};
