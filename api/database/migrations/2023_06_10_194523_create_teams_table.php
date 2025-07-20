`<?php

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
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug');
            $table->string('short_name')->nullable();
            $table->string('tla')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->string('website')->nullable();
            $table->string('founded')->nullable();
            $table->string('club_colors')->nullable();
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->unsignedBigInteger('coach_id')->nullable();

            $table->string('competition_id')->nullable();
            $table->unsignedBigInteger('continent_id')->default(0);
            $table->string('country_id')->nullable();
            $table->string('logo')->nullable();

            $table->dateTime('last_updated')->nullable();
            $table->dateTime('last_fetch')->nullable();
            $table->dateTime('last_detailed_fetch')->nullable();
            $table->integer('priority_number')->default(9999);
            $table->enum('gender', [1, 2]);
            
            $table->unsignedBigInteger('status_id')->default(1);
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
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
