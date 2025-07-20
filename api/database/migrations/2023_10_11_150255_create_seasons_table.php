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
        Schema::create('seasons', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('competition_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->unsignedInteger('current_matchday')->nullable();
            $table->unsignedInteger('total_matchdays')->nullable();
            $table->unsignedInteger('played_matches')->nullable();
            $table->unsignedBigInteger('winner_id')->nullable();
            $table->json('stages')->nullable();
            $table->boolean('fetched_standings')->default(false);
            $table->boolean('fetched_all_matches')->default(false);
            $table->boolean('fetched_all_single_matches')->default(false);
            $table->boolean('fetched_all_single_matches_odds')->default(false);

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
        Schema::dropIfExists('seasons');
    }
};
