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
        Schema::create('competition_prediction_statistics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('season_id')->nullable();
            $table->unsignedBigInteger('prediction_type_id')->default(0);
            $table->unsignedBigInteger('counts');

            $table->integer('full_time_home_wins_score')->nullable();
            $table->integer('full_time_draw_score')->nullable();
            $table->integer('full_time_away_wins_score')->nullable();
            $table->integer('hda_score')->nullable();

            $table->integer('gg_score')->nullable();
            $table->integer('ng_score')->nullable();

            $table->integer('over15_score')->nullable();
            $table->integer('under15_score')->nullable();

            $table->integer('over25_score')->nullable();
            $table->integer('under25_score')->nullable();

            $table->integer('over35_score')->nullable();
            $table->integer('under35_score')->nullable();

            $table->integer('cs_score')->nullable();

            $table->integer('accuracy_score')->nullable();
            $table->integer('precision_score')->nullable();
            $table->integer('f1_score')->nullable();
            $table->integer('average_score');

            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();

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
        Schema::dropIfExists('competition_prediction_statistics');
    }
};
