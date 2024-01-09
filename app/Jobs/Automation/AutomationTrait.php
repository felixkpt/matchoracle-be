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
}
