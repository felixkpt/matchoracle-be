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
        Schema::create('odds', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->dateTime('utc_date');
            $table->boolean('has_time');
            $table->string('home_team');
            $table->string('away_team');
            $table->decimal('home_win_odds', 5, 2);
            $table->decimal('draw_odds', 5, 2);
            $table->decimal('away_win_odds', 5, 2);
            $table->decimal('over_25_odds', 5, 2)->nullable();
            $table->decimal('under_25_odds', 5, 2)->nullable();
            $table->decimal('gg_odds', 5, 2)->nullable();
            $table->decimal('ng_odds', 5, 2)->nullable();
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('source_id');

            $table->unsignedInteger('status_id')->default(1);
            $table->uuid('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
