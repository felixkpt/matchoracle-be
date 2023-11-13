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
        Schema::create('game_scores', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('game_id');
            $table->string('winner')->nullable();
            $table->string('duration')->nullable();
            $table->string('home_scores_full_time')->nullable();
            $table->string('away_scores_full_time')->nullable();
            $table->string('home_scores_half_time')->nullable();
            $table->string('away_scores_half_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_scores');
    }
};
