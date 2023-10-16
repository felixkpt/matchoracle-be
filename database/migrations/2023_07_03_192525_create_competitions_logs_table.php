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
        Schema::create('competitions_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('competition_id');
            $table->unsignedInteger('fetch_counts')->default(0);
            $table->text('fetch_details')->nullable();
            $table->unsignedInteger('detailed_fetch_counts')->default(0);
            $table->text('detailed_fetch_details')->nullable();
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
        Schema::dropIfExists('competitions_logs');
    }
};
