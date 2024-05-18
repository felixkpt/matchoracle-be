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
        Schema::create('competition_prediction_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->unsignedInteger('prediction_type_id');
            $table->string('version')->default('1.0');
            $table->unsignedBigInteger('competition_id')->default(0);
            $table->date('date');

            $table->unsignedInteger('total_games');
            $table->unsignedInteger('total_predictable_games');
            $table->unsignedInteger('predicted_games')->default(0);
            $table->unsignedInteger('unpredicted_games')->default(0);

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
        Schema::dropIfExists('competition_prediction_logs');
    }
};
