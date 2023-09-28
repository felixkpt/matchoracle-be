<?php

use App\Models\TemporaryToken;
use App\Services\Filerepo\Models\ModelInstance;
use Illuminate\Support\Str;

if (!function_exists('getModelDetails')) {
    function getModelDetails($record)
    {
        $model_instance_id = null;
        $model_id = null;
        if ($record) {
            $model = get_class($record);
            $model_id = $record->id;
            $arr = ['name' => $model];
            $model_instance_id = ModelInstance::updateOrCreate($arr, $arr)->id;
        }

        return [0 => $model_instance_id, 1 => $model_id];
    }
}

if (!function_exists('previewFileIconSettings')) {
    function previewFileIconSettings()
    {
        return [];
    }
}

if (!function_exists('generateTemporaryToken')) {
    function generateTemporaryToken($expiration_minutes = 5)
    {
        $temporary_token = Str::random(6);
        $expirationTime = now()->addMinutes(config('temporary_token.expiration_minutes', $expiration_minutes));

        // Save the nonce to the database
        TemporaryToken::create([
            'token' => $temporary_token,
            'expires_at' => $expirationTime,
        ]);

        return $temporary_token;
    }
}

if (!function_exists('refreshTemporaryTokensInString')) {
    function refreshTemporaryTokensInString($content)
    {
        // Use preg_replace with a regular expression to replace the token
        $content = preg_replace('/\?token=\w{6}/', '?token=' . generateTemporaryToken(5), $content);

        return $content;
    }
}
