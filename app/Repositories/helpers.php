<?php

use App\Models\Continent;
use App\Models\Game;
use App\Models\GameScoreStatus;
use App\Models\GameSource;
use App\Models\Status;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use LaracraftTech\LaravelDynamicModel\DynamicModelFactory;
use Illuminate\Support\Str;

if (!function_exists('defaultColumns')) {

    function defaultColumns($model)
    {

        if (Schema::hasColumn($model->getTable(), 'user_id') && !$model->user_id)
            $model->user_id = auth()->id() ?? 0;

        if (Schema::hasColumn($model->getTable(), 'status_id') && !$model->status_id)
            $model->status_id = activeStatusId();

        if (Schema::hasColumn($model->getTable(), 'uuid') && !$model->uuid)
            $model->uuid = Str::uuid();


        return true;
    }
}

if (!function_exists('wasCreated')) {

    function wasCreated($model)
    {
        return !$model->wasRecentlyCreated && $model->wasChanged() ? true : false;
    }
}

if (!function_exists('respond')) {

    function respond($content, $status = 200, $type = 'json', $view = '', $headers = [])
    {

        if ($type == 'json' || request()->wantsJson())
            $res = response()->json($content, $status);
        elseif ($type == 'array')
            return $content;
        else if ($type == 'view')
            $res = view($view, $content);
        else
            $res = response($content, $status);

        return $res->withHeaders($headers);
    }
}

if (!function_exists('autoModel')) {

    function autoModel($table)
    {
        return app(DynamicModelFactory::class)->create(Game::class, $table);
    }
}

if (!function_exists('Created_at')) {
    function created_at($q)
    {
        return $q->created_at->diffForHumans();
    }
}

if (!function_exists('Created_by')) {
    function Created_by($q)
    {
        return getUser($q);
    }
}

if (!function_exists('getStatus')) {
    function getStatus($q)
    {
        $status = $q->status()->first();
        if ($status) {
            return '<div class="d-flex align-items-center"><iconify-icon icon="' . $status->icon . '" class="' . $status->class . ' me-1"></iconify-icon>' . Str::ucfirst(Str::replace('_', ' ', $status->name)) . '</div>';
        } else return null;
    }
}

if (!function_exists('getUser')) {
    function getUser($q)
    {
        $username = $q->user->name ?? 'System';
        return $username;
    }
}

if (!function_exists('actionLinks')) {
    function actionLinks($q, $uri, $view = 'modal', $edit = 'modal', $hide = null)
    {

        $a = '<li><a class="dropdown-item autotable-' . ($view === 'modal' ? 'modal-view' : 'navigate') . '" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">View</a></li>';
        $b = '<li><a class="dropdown-item autotable-' . ($edit === 'modal' ? 'modal-edit' : 'edit') . '" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '/edit">Edit</a></li>';
        $c = (!preg_match('#update-status#', $hide) ? '<li><a class="dropdown-item autotable-update-status" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '/update-status">Status update</a></li>' : '');

        $str = $a . $b . $c;

        return '
        <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="icon icon-list2 font-20"></i>
        </button>
        <ul class="dropdown-menu">
        ' . $str . '
        </ul>
        </div>
        ';
    }
}

if (!function_exists('activeStatusId')) {
    function activeStatusId()
    {
        return Status::where('name', 'active')->first()->id ?? 0;
    }
}

if (!function_exists('inActiveStatusId')) {
    function inActiveStatusId()
    {
        return Status::where('name', 'in_active')->first()->id ?? 0;
    }
}

if (!function_exists('gameScoresStatus')) {
    function gameScoresStatus($idOrSlug)
    {
        return GameScoreStatus::where(is_numeric($idOrSlug) ? 'id' : 'slug', $idOrSlug)->first()->id ?? 0;
    }
}

if (!function_exists('unsettledGameScoreStatuses')) {
    function unsettledGameScoreStatuses()
    {
        return [1100, 1101, 1104];
    }
}

if (!function_exists('game_scores')) {

    function game_scores($scoreData = null, $half_time = false)
    {
        // Get the score data or provide default values if it's missing
        if (!$half_time) {
            $homeTeamScore = $scoreData->home_scores_full_time ?? 0;
            $awayTeamScore = $scoreData->away_scores_full_time ?? 0;
        } else {
            $homeTeamScore = $scoreData->home_scores_half_time ?? 0;
            $awayTeamScore = $scoreData->away_scores_half_time ?? 0;
        }

        $scores = trim($homeTeamScore) . ' - ' . trim($awayTeamScore);
        $arr = scores();

        return $arr[$scores] ?? -1;
    }
}

if (!function_exists('scores')) {

    function scores()
    {

        $arr = [
            '0 - 0' => 0,
            '0 - 1' => 1,
            '0 - 2' => 2,
            '0 - 3' => 3,
            '0 - 4' => 4,
            '0 - 5' => 5,
            '0 - 6' => 6,
            '0 - 7' => 7,
            '0 - 8' => 8,
            '0 - 9' => 9,
            '0 - 10' => 10,
            '1 - 0' => 11,
            '1 - 1' => 12,
            '1 - 2' => 13,
            '1 - 3' => 14,
            '1 - 4' => 15,
            '1 - 5' => 16,
            '1 - 6' => 17,
            '1 - 7' => 18,
            '1 - 8' => 19,
            '1 - 9' => 20,
            '1 - 10' => 21,
            '2 - 0' => 22,
            '2 - 1' => 23,
            '2 - 2' => 24,
            '2 - 3' => 25,
            '2 - 4' => 26,
            '2 - 5' => 27,
            '2 - 6' => 28,
            '2 - 7' => 29,
            '2 - 8' => 30,
            '2 - 9' => 31,
            '2 - 10' => 32,
            '3 - 0' => 33,
            '3 - 1' => 34,
            '3 - 2' => 35,
            '3 - 3' => 36,
            '3 - 4' => 37,
            '3 - 5' => 37,
            '3 - 6' => 39,
            '3 - 7' => 40,
            '3 - 8' => 41,
            '3 - 9' => 42,
            '3 - 10' => 43,
            '4 - 0' => 44,
            '4 - 1' => 45,
            '4 - 2' => 46,
            '4 - 3' => 47,
            '4 - 4' => 48,
            '4 - 5' => 49,
            '4 - 6' => 50,
            '4 - 7' => 51,
            '4 - 8' => 52,
            '4 - 9' => 53,
            '4 - 10' => 54,
            '5 - 0' => 55,
            '5 - 1' => 56,
            '5 - 2' => 57,
            '5 - 3' => 58,
            '5 - 4' => 59,
            '5 - 5' => 60,
            '5 - 6' => 61,
            '5 - 7' => 62,
            '5 - 8' => 63,
            '5 - 9' => 64,
            '5 - 10' => 65,
            '6 - 0' => 66,
            '6 - 1' => 67,
            '6 - 2' => 68,
            '6 - 3' => 69,
            '6 - 4' => 70,
            '6 - 5' => 71,
            '6 - 6' => 72,
            '6 - 7' => 73,
            '6 - 8' => 74,
            '6 - 9' => 75,
            '6 - 10' => 76,
            '7 - 0' => 77,
            '7 - 1' => 78,
            '7 - 2' => 79,
            '7 - 3' => 80,
            '7 - 4' => 81,
            '7 - 5' => 82,
            '7 - 6' => 83,
            '7 - 7' => 84,
            '7 - 8' => 85,
            '7 - 9' => 86,
            '7 - 10' => 87,
            '8 - 0' => 88,
            '8 - 1' => 89,
            '8 - 2' => 90,
            '8 - 3' => 91,
            '8 - 4' => 92,
            '8 - 5' => 93,
            '8 - 6' => 94,
            '8 - 7' => 95,
            '8 - 8' => 96,
            '8 - 9' => 97,
            '8 - 10' => 98,
            '9 - 0' => 99,
            '9 - 1' => 100,
            '9 - 2' => 101,
            '9 - 3' => 102,
            '9 - 4' => 103,
            '9 - 5' => 104,
            '9 - 6' => 105,
            '9 - 7' => 106,
            '9 - 8' => 107,
            '9 - 9' => 108,
            '9 - 10' => 108,
            '10 - 0' => 110,
            '10 - 1' => 111,
            '10 - 2' => 112,
            '10 - 3' => 113,
            '10 - 4' => 114,
            '10 - 5' => 115,
            '10 - 6' => 116,
            '10 - 7' => 116,
            '10 - 8' => 118,
            '10 - 9' => 119,
            '10 - 10' => 120,

        ];

        return $arr;
    }
}


if (!function_exists('getUriFromUrl')) {
    function getUriFromUrl($url)
    {
        // Parse the URL to get its components
        $parsedUrl = parse_url($url);

        // Extract the path from the parsed URL
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';

        return $path;
    }
}

if (!function_exists('current_prediction_type')) {
    function current_prediction_type()
    {
        return request()->prediction_type_id ?? request()->current_prediction_type ?? 1108;
    }
}

if (!function_exists('default_source_id')) {
    function default_source_id()
    {
        return GameSource::where('url', 'https://www.forebet.com/')->first()->id ?? 0;
    }
}

if (!function_exists('is_connected')) {
    function is_connected()
    {
        try {
            fopen("http://www.google.com:80/", "r");
            return true;
        } catch (Exception $e) {
            Log::critical('Internet connectivity issue: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('get_world_id')) {
    function get_world_id()
    {
        return Continent::where('name', 'World')->first()->id ?? 0;
    }
}
