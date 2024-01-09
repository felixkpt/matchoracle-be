<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class Client
{
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

    /**
     * Perform an HTTP request and return the content.
     *
     * @param mixed $request
     * @return string|null
     */
    public static function get($request)
    {
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
        $response = self::sendRequest($request);
        return $response ? $response->getStatusCode() : null;
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

            File::ensureDirectoryExists(storage_path() . '/app/' . dirname($destinationPath));

            // Store the downloaded file content to the specified path
            Storage::disk('local')->put($filePath, $fileContent);

            // Return the path where the file is saved
            return $filePath;
        } catch (Exception $e) {
            // Log the exception for further analysis
            Log::error('Error creating directory: ' . $e->getMessage());
            return null;
        }
    }
}
