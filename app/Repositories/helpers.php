<?php

use App\Models\Game;
use App\Models\Status;
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

if (!function_exists('_dd')) {
    function _dd(...$args)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }
        http_response_code(500);

        call_user_func_array('dd', $args);
    }
}

if (!function_exists('Created_at')) {
    function created_at($q)
    {
        return $q->created_at->toDayDateTimeString();
    }
}

if (!function_exists('Created_by')) {
    function Created_by($q)
    {
        return $q->user->name ?? null;
    }
}

if (!function_exists('Status')) {
    function status($q)
    {
        $status = $q->status()->first();
        if ($status) {
            return '<div class="d-flex align-items-center"><iconify-icon icon="' . $status->icon . '" class="' . $status->class . ' me-1"></iconify-icon>' . Str::ucfirst(Str::replace('_', ' ', $status->name)) . '</div>';
        } else return null;
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
