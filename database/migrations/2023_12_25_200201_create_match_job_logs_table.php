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
        Schema::create('match_job_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->enum('task', ['recent_results', 'historical_results', 'fixtures', 'shallow_fixtures'])->default('recent_results');
            $table->date('date');
            $table->integer('source_id')->default(0);

            $table->integer('job_run_counts')->default(0);
            $table->integer('competition_run_counts')->default(0);
            $table->integer('action_run_counts')->default(0);
            $table->integer('average_seconds_per_action_run')->default(0);
            $table->integer('created_counts')->default(0);
            $table->integer('updated_counts')->default(0);
            $table->integer('failed_counts')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_job_logs');
    }
};
