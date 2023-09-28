<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    static function do($request)
    {

        try {
            $browser = new HttpBrowser(HttpClient::create());

            $browser->request('GET', $request);

            return $browser->getResponse();
        } catch (Exception $e) {
            Log::critical("Network error:", ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Do a http request
     * @param mixed $request
     * @return mixed
     */
    static function request($request)
    {
        $res = self::do($request);
        if ($res)
            return $res->getContent();
        else return $res;
    }

    /**
     * Do a http request
     * @param mixed $request
     * @return mixed
     */
    static function status($request)
    {
        $res = self::do($request);
        if ($res)
            return $res->getStatusCode();
        else return $res;
    }

    static function downloadFileFromUrl($url, $destinationPath)
    {

        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        if ($response->getStatusCode() === 200) {
            $content = $response->getContent();

            File::ensureDirectoryExists(Str::beforeLast(storage_path($destinationPath), '/'));
            Storage::put($destinationPath, $content);
            Storage::setVisibility($destinationPath, 'public');
            Storage::disk('public')->setVisibility($destinationPath, 'public');

            return true; // File downloaded successfully
        } else {
            return false; // Failed to download file
        }
    }
}
