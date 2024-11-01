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
     * @param mixed $request
     * @return string|null
     */
    public static function get($request, $proxy = 'puppet')
    {
        $response = self::fetchContentFromPuppeteer($request);
        return $response ? $response : null;

        $response = self::sendRequest($request);
        return $response ? $response->getContent() : null;
    }

    /**
     * Perform an HTTP request and return the status code.
     *
     * @param mixed $request
     * @return int|null
     */
    public static function requestStatus($request)
    {
        $response = self::fetchContentFromPuppeteer($request);
        return $response ? $response : null;

        
        $response = self::sendRequest($request);
        return $response ? $response->getStatusCode() : null;
    }

    /**
     * Perform an HTTP request and return the response.
     *
     * @param mixed $request
     * @return mixed|null
     */
    public static function sendRequest($request)
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

    public static function fetchContentFromPuppeteer($url)
    {
        $response = Http::timeout(70)->get('http://localhost:3075/fetch', [
            'url' => $url
        ]);

        if ($response->successful()) {
            $content = $response->body();
            return $content;
        } else {
            // Handle error response
            return 'Error fetching content: ' . $response->body();
        }
    }


    /**
     * Download a file from a given URL and save it to a specified destination path.
     *
     * @param string $url               The URL of the file to be downloaded.
     * @param string $destinationPath   The local destination path to save the downloaded file.
     *
     * @return string|null              The path where the file is saved, or null on failure.
     */
    public static function downloadFileFromUrl($url, $destinationPath)
    {
        // Combine destination path and filename
        $filePath = rtrim($destinationPath, '/');

        try {
            // Download the file content
            $fileContent = file_get_contents($url);

            if ($fileContent === false) {
                // Handle download failure
                return null;
            }
        } catch (Exception $e) {
            // Log the exception for further analysis
            Log::error('Error downloading content: ' . $e->getMessage());
            return null;
        }


        try {

            Log::info('filePath', [$filePath]);

            $path = $filePath;

            $disk = env('FILESYSTEM_DRIVER', 'local');

            if ($disk == 'gcs' && !str()->startsWith($path, config('app.gcs_project_folder'))) {
                $path = config('app.gcs_project_folder') . '/' . $path;
                // Remove repeated slashes
                $path = preg_replace("#/+#", "/", $path);
            }

            Log::info('filePath after:', [$path]);

            // Ensure the directory exists with the right permissions
            $directory = dirname($path);
            if (!Storage::disk($disk)->exists($directory)) {
                Storage::disk($disk)->makeDirectory($directory, 0755, true);
            }

            // Store the downloaded file content to the specified path and set its visibility to public
            Storage::disk($disk)->put($path, $fileContent);
            Storage::disk($disk)->setVisibility($path, 'public');


            // Return the filePath/path where the file is saved
            return $filePath;
        } catch (Exception $e) {
            // Log the exception for further analysis
            Log::error('Error creating directory: ' . $e->getMessage());
            return null;
        }
    }
}
