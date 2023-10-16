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
        Schema::create('competitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('code')->nullable();
            $table->string('type');
         
            $table->string('abbreviation')->nullable();
            $table->boolean('has_teams')->nullable();
            $table->uuid('continent_id')->default(0);
            $table->string('country_id')->nullable();
            $table->string('emblem')->nullable();
            $table->string('plan')->nullable();

            $table->dateTime('last_updated')->nullable();
            $table->unsignedInteger('number_of_available_seasons')->nullable()->default(0);
            $table->dateTime('last_fetch')->nullable();
            $table->dateTime('last_detailed_fetch')->nullable();
            $table->integer('priority_number')->default(9999);
            $table->uuid('stage_id')->default(0);
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
        Schema::dropIfExists('competitions');
    }
};
