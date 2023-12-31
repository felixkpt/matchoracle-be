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
        Schema::create('game_prediction_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedInteger('total_games');
            $table->unsignedInteger('predicted_games')->default(0);
            $table->unsignedInteger('unpredicted_games')->default(0);
            
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
        Schema::dropIfExists('game_prediction_logs');
    }
};
