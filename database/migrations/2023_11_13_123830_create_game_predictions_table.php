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
            $table->unsignedInteger('prediction_type_id')->default(1);
            $table->unsignedBigInteger('competition_id')->default(0);
            $table->dateTime('date');
            $table->unsignedBigInteger('game_id');
            $table->tinyInteger('hda')->default(-1);
            $table->integer('home_win_proba')->default(-1);
            $table->integer('draw_proba')->default(-1);
            $table->integer('away_win_proba')->default(-1);

            $table->tinyInteger('bts')->default(-1);
            $table->integer('gg_proba')->default(-1);
            $table->integer('ng_proba')->default(-1);

            $table->tinyInteger('over15')->default(-1);
            $table->integer('over15_proba')->default(-1);
            $table->integer('under15_proba')->default(-1);
            $table->tinyInteger('over25')->default(-1);
            $table->integer('over25_proba')->default(-1);
            $table->integer('under25_proba')->default(-1);
            $table->tinyInteger('over35')->default(-1);
            $table->integer('over35_proba')->default(-1);
            $table->integer('under35_proba')->default(-1);

            $table->integer('cs')->default(-1);
            $table->float('cs_proba')->default(-1);

            $table->boolean('is_normalized')->default(false);

            $table->unsignedBigInteger('status_id')->default(0);
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
