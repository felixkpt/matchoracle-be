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
     * Maximum safe size (in bytes) for response bodies we’ll allow.
     * 10MB is usually safe for HTML parsing.
     */
    const MAX_RESPONSE_SIZE = 10 * 1024 * 1024; // 10MB

    /**
     * Perform an HTTP request and return the content.
     *
     * @param string $request_url
     * @return string|null
     */
    public static function get($request_url)
    {
        $use_external_crawler_urls = config('app.use_external_crawler_urls');

        $response = $use_external_crawler_urls
            ? self::fetchContentFromPuppeteer($request_url)
            : self::sendRequest($request_url)->getContent();

        if ($response !== null) {
            $size = strlen($response);

            // Defensive check
            if ($size > self::MAX_RESPONSE_SIZE) {
                Log::warning("Response too large to process", [
                    'url' => $request_url,
                    'size_bytes' => $size
                ]);
                return null;
            }
        }

        return $response ?? null;
    }

    /**
     * Perform an HTTP request and return the status code.
     *
     * @param string $request_url
     * @return int|null
     */
    public static function requestStatus($request_url)
    {
        $use_external_crawler_urls = config('app.use_external_crawler_urls');

        $response = $use_external_crawler_urls
            ? self::fetchContentFromPuppeteer($request_url)
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
    public static function fetchContentFromPuppeteer($request_url)
    {
        $external_crawler_urls = config('app.external_crawler_urls');
        $index = random_int(0, count($external_crawler_urls) - 1);
        $crawler_url = $external_crawler_urls[$index] . '/fetch';

        $response = Http::timeout(70)->get($crawler_url, ['url' => $request_url]);

        if ($response->successful()) {
            $body = $response->body();
            $size = strlen($body);

            if ($size > self::MAX_RESPONSE_SIZE) {
                Log::warning("External crawler returned oversized response", [
                    'url' => $request_url,
                    'size_bytes' => $size
                ]);
                return null;
            }

            return $body;
        } else {
            Log::error('Error fetching content: ' . $response->body());
            return null;
        }
    }


    /**
     * Fetch content using Puppeteer.
     *
     * @param string $request_url
     * @return string|null
     */
    public static function fetchContentFromPuppeteerV2($request_url)
    {
        $external_crawler_urls = config('app.external_crawler_urls');

        foreach ($external_crawler_urls as $crawler_base) {
            // Check crawler status first
            try {
                $statusResponse = Http::timeout(5)->get($crawler_base . '/status');
                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    if (!empty($statusData['status']) && $statusData['status'] === 'free') {
                        $crawler_url = $crawler_base . '/fetch';

                        $response = Http::timeout(70)->get($crawler_url, [
                            'url' => $request_url
                        ]);

                        if ($response->successful()) {
                            $body = $response->body();
                            $size = strlen($body);

                            if ($size > self::MAX_RESPONSE_SIZE) {
                                Log::warning("External crawler returned oversized response", [
                                    'url' => $request_url,
                                    'size_bytes' => $size
                                ]);
                                return null;
                            }

                            return $body;
                        } else {
                            Log::error('Crawler fetch error: ' . $response->body());
                            return null;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Error checking crawler {$crawler_base}: " . $e->getMessage());
                continue; // Try next crawler
            }
        }

        // If no crawler is free
        Log::error('No available crawler found for request: ' . $request_url);
        return null;
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
