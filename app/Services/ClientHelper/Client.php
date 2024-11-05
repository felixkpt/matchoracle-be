<?php

namespace App\Services\ClientHelper;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
    /**
     * Perform an HTTP request and return the content.
     *
     * @param string $request_url
     * @param boolean $external_crawler_key
     * @return string|null
     */
    public static function get($request_url, $external_crawler_key = null)
    {
        $response = $external_crawler_key
            ? self::fetchContentFromPuppeteer($request_url, $external_crawler_key)
            : self::sendRequest($request_url)->getContent();

        return $response ?? null;
    }

    /**
     * Perform an HTTP request and return the status code.
     *
     * @param string $request_url
     * @param boolean $external_crawler_key
     * @return int|null
     */
    public static function requestStatus($request_url, $external_crawler_key = null)
    {
        $response = $external_crawler_key
            ? self::fetchContentFromPuppeteer($request_url, $external_crawler_key)
            : self::sendRequest($request_url)->getStatusCode();

        return $response ?? null;
    }

    /**
     * Perform an HTTP request and return the response.
     *
     * @param string $request_url
     * @return mixed|null
     */
    public static function sendRequest($request_url)
    {
        try {
            $browser = new HttpBrowser(HttpClient::create());
            $browser->request('GET', $request_url);
            return $browser->getResponse();
        } catch (Exception $e) {
            Log::critical("Network error:", ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch content using Puppeteer.
     *
     * @param string $request_url
     * @return string|null
     */
    public static function fetchContentFromPuppeteer($request_url, $external_crawler_key = null)
    {
        $external_crawler_urls = config('external_crawler_urls');

        $crawler_url = $external_crawler_urls[$external_crawler_key] . '/fetch';

        $response = Http::timeout(70)->get($crawler_url, ['url' => $request_url]);

        if ($response->successful()) {
            return $response->body();
        } else {
            // Handle error response
            Log::error('Error fetching content: ' . $response->body());
            return null;
        }
    }

    /**
     * Download a file from a given URL and save it to a specified destination path.
     *
     * @param string $url
     * @param string $destinationPath
     * @return string|null
     */
    public static function downloadFileFromUrl($url, $destinationPath)
    {
        $filePath = rtrim($destinationPath, '/');

        try {
            $fileContent = file_get_contents($url);
            if ($fileContent === false) {
                return null;
            }
        } catch (Exception $e) {
            Log::error('Error downloading content: ' . $e->getMessage());
            return null;
        }

        try {
            Log::info('filePath', [$filePath]);

            $disk = env('FILESYSTEM_DRIVER', 'local');
            if ($disk === 'gcs' && !str()->startsWith($filePath, config('app.gcs_project_folder'))) {
                $filePath = config('app.gcs_project_folder') . '/' . $filePath;
                $filePath = preg_replace("#/+#", "/", $filePath);
            }

            if (!Storage::disk($disk)->exists(dirname($filePath))) {
                Storage::disk($disk)->makeDirectory(dirname($filePath), 0755, true);
            }

            Storage::disk($disk)->put($filePath, $fileContent);
            Storage::disk($disk)->setVisibility($filePath, 'public');

            return $filePath;
        } catch (Exception $e) {
            Log::error('Error creating directory: ' . $e->getMessage());
            return null;
        }
    }
}
