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
        Schema::create('game_score_statuses', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->string('name')->unique();
            $table->string('slug')->nullable()->unique();
            $table->text('description');
            $table->boolean('is_fully_settled')->default(0);
            $table->string('icon')->nullable();
            $table->string('class')->nullable();
            $table->unsignedBigInteger('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_score_statuses');
    }
};
