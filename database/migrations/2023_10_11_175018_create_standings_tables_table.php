<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('standing_tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('season_id');
            $table->uuid('standing_id');
            $table->uuid('team_id');
            $table->unsignedInteger('position');
            $table->unsignedInteger('played_games');
            $table->string('form')->nullable();
            $table->unsignedInteger('won');
            $table->unsignedInteger('draw');
            $table->unsignedInteger('lost');
            $table->unsignedInteger('points');
            $table->unsignedInteger('goals_for');
            $table->unsignedInteger('goals_against');
            $table->integer('goal_difference');
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
        Schema::dropIfExists('standing_tables');
    }
};
