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
        Schema::create('game_referee', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->unsignedBigInteger('game_id')->default(0);
            $table->unsignedBigInteger('referee_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_referee');
    }
};
