<?php

use App\Models\Game;
use LaracraftTech\LaravelDynamicModel\DynamicModelFactory;

if (!function_exists('defaultColumns')) {

    function defaultColumns($model)
    {
        // $model->uuid = Str::orderedUuid();
        $model->user_id = auth()->id() ?? 'Jkhgz3nrd9Vuvr3Vlcne6zp86i';
        return true;
    }

    function wasCreated($model)
    {
        return !$model->wasRecentlyCreated && $model->wasChanged() ? true : false;
    }
    
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
