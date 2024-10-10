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
        Schema::create('standing_job_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->enum('task', ['recent_results', 'historical_results'])->default('recent_results');
            $table->date('date');
            $table->integer('source_id')->default(0);

            $table->integer('job_run_counts')->default(0);
            $table->integer('competition_run_counts')->default(0);
            $table->integer('fetch_run_counts')->default(0);
            $table->integer('fetch_success_counts')->default(0);
            $table->integer('fetch_failed_counts')->default(0);

            $table->integer('average_minutes_per_run')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standing_job_logs');
    }
};
