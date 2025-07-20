<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('code')->nullable();
            $table->enum('category', ['domestic', 'international'])->nullable();
            $table->string('type')->nullable();

            $table->string('abbreviation')->nullable();
            $table->boolean('has_standings')->default(true);
            $table->boolean('has_teams')->nullable();
            $table->boolean('is_odds_enabled')->nullable();
            $table->unsignedBigInteger('continent_id')->default(0);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('plan')->nullable();

            $table->unsignedInteger('games_per_season')->nullable()->default(0);
            $table->unsignedInteger('available_seasons')->nullable()->default(0);
            $table->integer('priority_number')->default(9999);
            $table->unsignedBigInteger('stage_id')->default(0);
            $table->enum('gender', [1, 2]);

            $table->unsignedBigInteger('games_counts')->nullable()->default(0);
            $table->unsignedBigInteger('predictions_counts')->nullable()->default(0);
            $table->unsignedBigInteger('odds_counts')->nullable()->default(0);

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
        Schema::dropIfExists('competitions');
    }
};
