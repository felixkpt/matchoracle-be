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
        Schema::create('betting_tips_statistics', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('type');
            $table->boolean('is_multiples')->default(false);
            $table->string('range');

            $table->decimal('initial_bankroll');
            $table->unsignedInteger('bankroll_topups')->default(1);
            $table->decimal('final_bankroll');

            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('won')->default(0);
            $table->unsignedInteger('won_percentage')->default(0);
            $table->decimal('average_won_odds')->default(0);
            $table->decimal('gain');
            $table->integer('roi');
            $table->unsignedInteger('longest_winning_streak')->default(0);
            $table->unsignedInteger('longest_losing_streak')->default(0);

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
        Schema::dropIfExists('betting_tips_statistics');
    }
};
