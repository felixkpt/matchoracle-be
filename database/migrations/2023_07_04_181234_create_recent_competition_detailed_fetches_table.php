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
        Schema::create('recent_competition_detailed_fetches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->year('year');
            $table->uuid('competition_id');
            // Add the unique constraint to `year` and `competition_id`
            $table->unique(['year', 'competition_id']);
            $table->dateTime('fetched_at')->nullable();
            $table->uuid('status_id')->default(0);
            $table->uuid('user_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recent_competition_detailed_fetches');
    }
};
