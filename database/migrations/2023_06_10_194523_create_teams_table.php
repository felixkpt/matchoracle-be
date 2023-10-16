<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('short_name')->nullable();
            $table->string('tla')->nullable();
            $table->uuid('address_id')->nullable();
            $table->string('website')->nullable();
            $table->string('founded')->nullable();
            $table->string('club_colors')->nullable();
            $table->uuid('venue_id')->nullable();
            $table->uuid('coach_id')->nullable();

            $table->string('competition_id')->nullable();
            $table->uuid('continent_id')->default(0);
            $table->string('country_id')->nullable();
            $table->string('crest')->nullable();

            $table->dateTime('last_updated')->nullable();
            $table->dateTime('last_fetch')->nullable();
            $table->dateTime('last_detailed_fetch')->nullable();
            $table->integer('priority_number')->default(9999);
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
        Schema::dropIfExists('teams');
    }
};
