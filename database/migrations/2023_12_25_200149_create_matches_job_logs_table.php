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
        Schema::create('matches_job_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->date('date');
            $table->integer('source_id')->default(0);

            $table->integer('job_run_counts')->default(0);
            $table->integer('competition_run_counts')->default(0);
            $table->integer('fetch_run_counts')->default(0);
            $table->integer('fetch_success_counts')->default(0);
            $table->integer('fetch_failed_counts')->default(0);
            
            $table->integer('updated_matches_counts')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches_job_logs');
    }
};
