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
        Schema::create('competition_prediction_type_statistics', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->string('prediction_type');
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('train_counts');
            $table->unsignedBigInteger('test_counts');
            $table->unsignedBigInteger('score_target_outcome_id');
            $table->json('occurrences');
            $table->json('last_predicted');

            $table->integer('accuracy_score')->nullable();
            $table->integer('precision_score')->nullable();
            $table->integer('f1_score')->nullable();
            $table->integer('average_score');
            $table->date('from_date');
            $table->date('to_date');

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
        Schema::dropIfExists('competition_prediction_type_statistics');
    }
};
