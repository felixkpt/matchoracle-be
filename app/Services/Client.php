<?php

namespace App\Services;

use App\Services\Filerepo\FileRepo;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TimeoutExceptionInterface;

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
        } catch (ClientExceptionInterface | ServerExceptionInterface $e) {
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
    public static function requestContent($request)
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


    public static function downloadFileFromUrl($url, $destinationPath, $filename = null, $record = null)
    {
        try {
            $client = HttpClient::create();
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {

                $content = $response->getContent();

                // Generate a temporary file path to store the content
                $tempFilePath = tempnam(sys_get_temp_dir(), 'temp_') . $filename;

                // Save the content to the temporary file
                file_put_contents($tempFilePath, $content);

                // Create a Symfony File instance from the temporary file
                $file = new File($tempFilePath);

                // Create an UploadedFile instance from the Symfony File object
                $uploadedFile = new UploadedFile(
                    $file->getRealPath(),
                    $filename,
                    $file->getMimeType(),
                    $file->getSize(),
                    false,
                    true
                );

                // Save the uploaded file using FileRepo
                FileRepo::uploadFile($record, $uploadedFile, $destinationPath, $filename, null, true, null, 1, !!$record);

                // Remove the temporary file
                unlink($tempFilePath);

                return $destinationPath . '/' . $filename; // File downloaded and saved successfully, return the file path

            } else {
                return null; // Failed to download file, return null
            }
        } catch (TransportExceptionInterface | TimeoutExceptionInterface $e) {
            // Handle network errors
            Log::critical("Network error:", ['message' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            // Handle any other unexpected errors
            Log::error("Unexpected error:", ['message' => $e->getMessage()]);
            return null;
        }
    }
}
