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
            
            $table->unsignedBigInteger('ht_home_wins');
            $table->unsignedBigInteger('ht_draws');
            $table->unsignedBigInteger('ht_away_wins');
            $table->unsignedBigInteger('ft_home_wins');
            $table->unsignedBigInteger('ft_draws');
            $table->unsignedBigInteger('ft_away_wins');
            $table->unsignedBigInteger('ft_gg');
            $table->unsignedBigInteger('ft_ng');
            $table->unsignedBigInteger('ft_over15');
            $table->unsignedBigInteger('ft_under15');
            $table->unsignedBigInteger('ft_over25');
            $table->unsignedBigInteger('ft_under25');
            $table->unsignedBigInteger('ft_over35');
            $table->unsignedBigInteger('ft_under35');

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
