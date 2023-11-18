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
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('version')->default('1.0');
            $table->string('type')->default('regular');
            $table->string('competition_id')->default(0);
            $table->date('date');
            $table->uuid('game_id');
            $table->tinyInteger('hda')->default(-1);
            $table->integer('home_win_proba')->default(-1);
            $table->integer('draw_proba')->default(-1);
            $table->integer('away_win_proba')->default(-1);
            
            $table->tinyInteger('bts')->default(-1);
            $table->integer('gg_proba')->default(-1);
            $table->integer('ng_proba')->default(-1);
            
            $table->tinyInteger('over15')->default(-1);
            $table->integer('over12_proba')->default(-1);
            $table->integer('under12_proba')->default(-1);
            $table->tinyInteger('over25')->default(-1);
            $table->integer('over25_proba')->default(-1);
            $table->integer('under25_proba')->default(-1);
            $table->tinyInteger('over35')->default(-1);
            $table->integer('over35_proba')->default(-1);
            $table->integer('under35_proba')->default(-1);
            
            $table->integer('cs_unsensored')->default(-1);
            $table->float('cs_proba_unsensored')->default(-1);
            $table->integer('cs')->default(-1);
            $table->float('cs_proba')->default(-1);

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
        Schema::dropIfExists('game_predictions');
    }
};
