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
        Schema::create('game_odd', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('odd_id')->nullable();

            $table->unsignedInteger('status_id')->default(1);
            $table->uuid('user_id')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_odd');
    }
};
