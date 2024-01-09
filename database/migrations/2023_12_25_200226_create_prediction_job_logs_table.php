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
        Schema::create('prediction_job_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->string('version')->default('1.0');
            $table->unsignedInteger('prediction_type_id');
            $table->date('date');

            $table->integer('job_run_counts')->default(0);
            $table->integer('competition_run_counts')->default(0);
            $table->integer('prediction_run_counts')->default(0);
            $table->integer('prediction_success_counts')->default(0);
            $table->integer('prediction_failed_counts')->default(0);
            
            $table->integer('predicted_counts')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_job_logs');
    }
};
