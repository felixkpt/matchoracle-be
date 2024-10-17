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
        Schema::create('competition_prediction_statistics', function (Blueprint $table) {
            $table->bigIncrements('id')->startingValue(1100);
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('competition_id');
            $table->unsignedBigInteger('season_id')->nullable();
            $table->unsignedBigInteger('prediction_type_id')->default(0);

            $table->date('date')->nullable();
            $table->integer('matchday')->nullable();

            $table->unsignedBigInteger('ft_counts');
            $table->integer('ft_home_wins_counts');
            $table->integer('ft_home_wins_preds');
            $table->integer('ft_home_wins_preds_true');
            $table->integer('ft_home_wins_preds_true_percentage');

            $table->integer('ft_draws_counts');
            $table->integer('ft_draws_preds');
            $table->integer('ft_draws_preds_true');
            $table->integer('ft_draws_preds_true_percentage');

            $table->integer('ft_away_wins_counts');
            $table->integer('ft_away_wins_preds');
            $table->integer('ft_away_wins_preds_true');
            $table->integer('ft_away_wins_preds_true_percentage');

            $table->integer('ft_gg_counts');
            $table->integer('ft_gg_preds');
            $table->integer('ft_gg_preds_true');
            $table->integer('ft_gg_preds_true_percentage');

            $table->integer('ft_ng_counts');
            $table->integer('ft_ng_preds');
            $table->integer('ft_ng_preds_true');
            $table->integer('ft_ng_preds_true_percentage');

            $table->integer('ft_over15_counts');
            $table->integer('ft_over15_preds');
            $table->integer('ft_over15_preds_true');
            $table->integer('ft_over15_preds_true_percentage');

            $table->integer('ft_under15_counts');
            $table->integer('ft_under15_preds');
            $table->integer('ft_under15_preds_true');
            $table->integer('ft_under15_preds_true_percentage');

            $table->integer('ft_over25_counts');
            $table->integer('ft_over25_preds');
            $table->integer('ft_over25_preds_true');
            $table->integer('ft_over25_preds_true_percentage');

            $table->integer('ft_under25_counts');
            $table->integer('ft_under25_preds');
            $table->integer('ft_under25_preds_true');
            $table->integer('ft_under25_preds_true_percentage');

            $table->integer('ft_over35_counts');
            $table->integer('ft_over35_preds');
            $table->integer('ft_over35_preds_true');
            $table->integer('ft_over35_preds_true_percentage');

            $table->integer('ft_under35_counts');
            $table->integer('ft_under35_preds');
            $table->integer('ft_under35_preds_true');
            $table->integer('ft_under35_preds_true_percentage');

            $table->integer('accuracy_score')->nullable();
            $table->integer('precision_score')->nullable();
            $table->integer('f1_score')->nullable();
            $table->integer('average_score');

            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();

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
        Schema::dropIfExists('competition_prediction_statistics');
    }
};
