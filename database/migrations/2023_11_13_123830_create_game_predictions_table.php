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
        Schema::create('game_predictions', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('prediction_type_id');
            $table->string('version')->default('1.0');
            $table->unsignedBigInteger('competition_id')->default(0);
            $table->dateTime('date');
            $table->unsignedBigInteger('game_id');
          
            $table->tinyInteger('ft_hda_pick')->nullable();
            $table->integer('ft_home_win_proba')->nullable();
            $table->integer('ft_draw_proba')->nullable();
            $table->integer('ft_away_win_proba')->nullable();

            $table->tinyInteger('ht_hda_pick')->nullable();
            $table->integer('ht_home_win_proba')->nullable();
            $table->integer('ht_draw_proba')->nullable();
            $table->integer('ht_away_win_proba')->nullable();

            $table->tinyInteger('bts_pick')->nullable();
            $table->integer('gg_proba')->nullable();
            $table->integer('ng_proba')->nullable();

            $table->tinyInteger('over_under15_pick')->nullable();
            $table->integer('over15_proba')->nullable();
            $table->integer('under15_proba')->nullable();

            $table->tinyInteger('over_under25_pick')->nullable();
            $table->integer('over25_proba')->nullable();
            $table->integer('under25_proba')->nullable();

            $table->tinyInteger('over_under35_pick')->nullable();
            $table->integer('over35_proba')->nullable();
            $table->integer('under35_proba')->nullable();


            $table->integer('cs')->nullable();
            $table->float('cs_proba')->nullable();

            $table->boolean('is_normalized')->default(false);

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
        Schema::dropIfExists('game_predictions');
    }
};
