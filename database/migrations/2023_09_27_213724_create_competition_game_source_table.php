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
        Schema::create('competition_game_source', function (Blueprint $table) {
            $table->unsignedBigInteger('competition_id')->default(0);
            $table->unsignedBigInteger('game_source_id')->default(0);
            $table->string('uri')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('subscription_expires')->nullable();
            $table->boolean('is_subscribed')->default(0);
            $table->unsignedInteger('priority_number')->default(9999);
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
        Schema::dropIfExists('competition_game_source');
    }
};
