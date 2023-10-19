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
        Schema::create('recent_team_detailed_fetches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->year('year');
            $table->uuid('team_id');
            // Add the unique constraint to `year` and `team_id`
            $table->unique(['year', 'team_id']);
            $table->dateTime('fetched_at')->nullable();
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
        Schema::dropIfExists('recent_team_detailed_fetches');
    }
};
