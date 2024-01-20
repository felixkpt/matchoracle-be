<?php

namespace App\Http\Controllers\Admin\Settings\System;

use App\Http\Controllers\Controller;
use App\Models\SeasonJobLog;
use App\Models\MatchesJobLog;
use App\Models\MatchJobLog;
use App\Models\PredictionJobLog;
use App\Models\StandingJobLog;
use App\Repositories\SearchRepo;
use Illuminate\Support\Carbon;

class LogsController extends Controller
{
    public function index()
    {
        $data = [];

        return response(['results' => $data]);
    }

    public function seasonsJobLogs()
    {
        $model = SeasonJobLog::query();
        
        $data = SearchRepo::of($model, ['date'])
            ->addColumn('Last_run', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn ($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();

        return response(['results' => $data]);
    }

    public function standingsJobLogs()
    {
        $model = StandingJobLog::query()
        ->when(request()->task, fn ($q) => $q->where('task', request()->task));
        
        $data = SearchRepo::of($model, ['date'])
            ->addColumn('Last_run', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn ($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();

        return response(['results' => $data]);
    }

    public function matchesJobLogs()
    {
        $model = MatchesJobLog::query()
        ->when(request()->task, fn ($q) => $q->where('task', request()->task));

        $data = SearchRepo::of($model, ['date'])
            ->addColumn('Last_run', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn ($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();

        return response(['results' => $data]);
    }

    public function matchJobLogs()
    {
        $model = MatchJobLog::query()
            ->when(request()->task, fn ($q) => $q->where('task', request()->task));

        $data = SearchRepo::of($model, ['date'])
            ->addColumn('Last_run', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn ($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();

        return response(['results' => $data]);
    }

    public function predictionsJobLogs()
    {
        $model = PredictionJobLog::query();
        $data = SearchRepo::of($model, ['date'])
            ->addColumn('Last_run', fn ($q) => Carbon::parse($q->updated_at)->diffForHumans())
            ->addColumn('Created_at', fn ($q) => Carbon::parse($q->created_at)->diffForHumans())
            ->paginate();


        return response(['results' => $data]);
    }
}
