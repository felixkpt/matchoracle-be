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
        Schema::create('teams_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('team_id');
            $table->unsignedInteger('fetch_counts')->default(0);
            $table->text('fetch_details')->nullable();
            $table->unsignedInteger('detailed_fetch_counts')->default(0);
            $table->text('detailed_fetch_details')->nullable();
            $table->unsignedBigInteger('status_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams_logs');
    }
};
