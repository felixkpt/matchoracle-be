<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompetitionPredictionStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        dd('Yep');
        // $table->uuid('uuid')->unique();
        // $table->unsignedBigInteger('prediction_type_id')->default(0);
        // $table->unsignedBigInteger('prediction_counts');

        // $table->integer('full_time_home_wins_score')->nullable();
        // $table->integer('full_time_draw_score')->nullable();
        // $table->integer('full_time_away_wins_score')->nullable();
        // $table->integer('hda_score')->nullable();

        // $table->integer('gg_score')->nullable();
        // $table->integer('ng_score')->nullable();

        // $table->integer('over15_score')->nullable();
        // $table->integer('under15_score')->nullable();

        // $table->integer('over25_score')->nullable();
        // $table->integer('under25_score')->nullable();

        // $table->integer('over35_score')->nullable();
        // $table->integer('under35_score')->nullable();

        // $table->integer('cs_score')->nullable();

        // $table->integer('accuracy_score')->nullable();
        // $table->integer('precision_score')->nullable();
        // $table->integer('f1_score')->nullable();
        // $table->integer('average_score');

        // $table->date('from_date');
        // $table->date('to_date');

        // $table->unsignedBigInteger('status_id')->default(0);
        // $table->unsignedBigInteger('user_id')->default(0)->nullable();
    }
}
