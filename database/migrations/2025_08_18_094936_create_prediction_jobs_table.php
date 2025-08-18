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
        Schema::create('prediction_jobs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);

            $table->string('process_id')->unique();
            $table->string('status')->default('pending');

            // morphable relationship to competitions or other models
            $table->nullableMorphs('morphable');

            // scheduling + attempts (UNIX int style like Laravel's queue)
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at')->nullable();
            $table->unsignedInteger('created_at');  // job pushed time

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prediction_jobs');
    }
};
