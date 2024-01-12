<?php

namespace App\Jobs\Automation;

use Illuminate\Support\Carbon;

trait AutomationTrait
{

    private function doCompetitionRunLogging()
    {

        $record = $this->loggerModel();

        if ($record) {
            $record->update(['competition_run_counts' => $record->competition_run_counts + 1]);
        }
    }

    private function logFailure($model, $data)
    {
        $model->create(['date' => Carbon::now(), 'message' => $data['message']]);
    }

    private function updateLastFetch($competition, $seasons, $column)
    {
        if ($seasons == 'from_seasons' || $seasons->count() > 0) {
            $competition->$column = now();
            $competition->save();
        }
    }
}
