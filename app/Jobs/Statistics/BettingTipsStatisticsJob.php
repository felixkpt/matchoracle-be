<?php

namespace App\Jobs\Statistics;

use App\Http\Controllers\Dashboard\BettingTips\BettingTipsController;
use App\Jobs\Automation\AutomationTrait;
use App\Models\BettingTipsStatistic;
use App\Models\BettingTipsStatisticJobLog;
use App\Repositories\BettingTips\Core\TipsListForCore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class BettingTipsStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The tip type for which statistics should be generated.
     *
     * @var int|null
     */
    private $type = 'home_win_tips';

    /**
     * Create a new job instance.
     *
     * @param int|null $competitionId
     */
    public function __construct($type = null)
    {
        $this->type = $type;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $types = $this->type ? [$this->type] : array_keys((new TipsListForCore())->tipClasses);

        request()->merge(['without_response' => true, 'prediction_mode_id' => 1]);

        $dates = [
            [
                'label' => 'Last 1 year',
                'dates' => [
                    Carbon::now()->subMonths(12)->format('Y-m-d'),
                    Carbon::now()->yesterday()->format('Y-m-d'),
                ]
            ],
            [
                'label' => 'Last 6 months',
                'dates' => [
                    Carbon::now()->subMonths(6)->format('Y-m-d'),
                    Carbon::now()->yesterday()->format('Y-m-d'),
                ]
            ],
            [
                'label' => 'Last 3 months',
                'dates' => [
                    Carbon::now()->subMonths(3)->format('Y-m-d'),
                    Carbon::now()->yesterday()->format('Y-m-d'),
                ]
            ],
            [
                'label' => 'Last 1 month',
                'dates' => [
                    Carbon::now()->subMonths(1)->format('Y-m-d'),
                    Carbon::now()->yesterday()->format('Y-m-d'),
                ]
            ]
        ];

        foreach ([false, true] as $is_multiples) {
            request()->merge(['multiples' => $is_multiples]);
            $multiples = $is_multiples ? 'Yes' : 'No';
            echo "Is Multiples: {$multiples}\n";

            foreach ($dates as $date) {
                $from_date = $date['dates'][0];
                $to_date = $date['dates'][1];
                request()->merge(['from_date' => $from_date, 'to_date' => $to_date]);

                $range = $date['label'];
                echo "{$range} ({$from_date} - {$to_date}):\n";

                foreach ($types as $type) {
                    request()->merge(['type' => $type, 'range' => $range,]);

                    echo "Type: {$type}\n";

                    $this->loggerModel(true);

                    $data = app(BettingTipsController::class)->index();

                    $results = $data['results']['investment'];
                    BettingTipsStatistic::updateOrCreate(
                        [
                            'type' => $type,
                            'is_multiples' => $is_multiples,
                            'range' => $range,

                        ],
                        [
                            'type' => $type,
                            'is_multiples' => $is_multiples,
                            'range' => $range,

                            'initial_bankroll' => $results['initial_bankroll']  ?? 0,
                            'bankroll_topups' => $results['bankroll_topups']  ?? 0,
                            'final_bankroll' => $results['final_bankroll']  ?? 0,
                            'total' => $results['total']  ?? 0,
                            'won' => $results['won']  ?? 0,
                            'won_percentage' => $results['won_percentage']  ?? 0,
                            'average_won_odds' => $results['average_won_odds']  ?? 0,
                            'gain' => $results['gain']  ?? 0,
                            'roi' => $results['roi']  ?? 0,
                            'longest_winning_streak' => $results['longest_winning_streak']  ?? 0,
                            'longest_losing_streak' => $results['longest_losing_streak']  ?? 0,
                        ]
                    );

                    $this->doLogging($data);

                    echo "------------\n";
                }
            }
        }
    }

    private function doLogging($data = null)
    {

        $games_run_counts = $data['results']['investment']['total'] ?? 0;
        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'games_run_counts' => $exists->games_run_counts + $games_run_counts,
                'types_run_counts' => $exists->types_run_counts + 1,
            ];

            $exists->update($arr);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {

        $date = today();

        $record = BettingTipsStatisticJobLog::where('type', request()->type)
            ->where('range', request()->range)
            ->where('date', $date)
            ->first();

        if (!$record) {
            $arr = [
                'type' => request()->type,
                'range' => request()->range,
                'date' => $date,
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'games_run_counts' => 0,
            ];

            $record = BettingTipsStatisticJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
