<?php

namespace App\Services\Filerepo\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TemporaryToken;
use App\Services\Filerepo\FileRepo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Carbon\Carbon;

class FilesController extends Controller
{

    protected $files_folder;
    protected $delete_url = "admin/file-repo/tmp/delete";

    function __construct()
    {
    }

    /**
     * loadDropzone -> store files
     */
    public function saveFiles($modelRecord = null, $files = null)
    {

        if (request()->clear == 1)
            return FileRepo::deleteOldTempFiles();

        $this->files_folder = $modelRecord ? strtolower(Str::plural(class_basename($modelRecord))) : (request()->files_folder ?? 'uncategorized');

        $files = $files ?? (request()->file('files_array') ?: []);

        $output = [];
        foreach ($files as $key => $image) {

            // please check ini_get('upload_max_filesize')
            if ($image->getSize() == null) {
                return [
                    'error' => 'Cannot determine file size, seems like the file has exceeded upload max filesize.',
                ];
            }

            $tmp_file_name = $image->getClientOriginalName();
            $ext = $image->getClientOriginalExtension();
            $base_name = Str::slug(pathinfo($tmp_file_name, PATHINFO_FILENAME)); // Get the base name without extension
            $file_name = Carbon::now()->format('Y/m/d') . '/' . $base_name . "." . $ext;
            $path = $this->files_folder . '/' . $file_name;

            try {
                $file_type = self::getFileType($image->getMimeType());
                $res = [
                    'caption' => $tmp_file_name,
                    'type' => $file_type,
                    'size' => $image->getSize(),
                    'path' => $this->files_folder . '/' . $file_name
                ];

                if (!Storage::disk(env('FILESYSTEM_DRIVER', 'local'))->exists($path)) {

                    $file = $image;
                    $folder = $this->files_folder;
                    $record = FileRepo::uploadFile($modelRecord, $file, $folder, $file_name, 0, true,);

                    // skip to next array key if failed to save file
                    if ($record === false) continue;

                    array_push($output, $res);
                    $preview[] = Storage::disk(env('FILESYSTEM_DRIVER', 'local'))->url($record->path);
                } else {
                    array_push($output, $res);
                    $preview[] = Storage::disk(env('FILESYSTEM_DRIVER', 'local'))->url($path);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $res = [
                    'error' => $error,
                ];
                array_push($output, $res);
            }
        }

        return $output;
    }

    public static function getFileType($mimeType)
    {

        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'];

        if ($mimeType == "application/pdf") {
            $file = "pdf";
        } elseif ($mimeType == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
            $file = "office";
        } elseif ($mimeType == "text/plain") {
            $file = "text";
        } elseif ($mimeType == "application/octet-stream") {
            $file = "office";
        } elseif ($mimeType == "application/msword") {
            $file = "office";
        } elseif ($mimeType == "audio/wav") {
            $file = "audio";
        } elseif (!in_array($mimeType, $allowedMimeTypes)) {
            $file = "image";
        } else {
            $file = "image";
        }
        return $file;
    }

    public function uploadImage()
    {
        $res = $this->saveFiles(null, [request()->file('image')])[0];

        return response(['message' => 'Image uploaded.', 'results' => ['data' => $res, 'token' => generateTemporaryToken()]]);
    }

    public function show($path)
    {
        $filePath = $path;

        if (Storage::disk('local')->exists($filePath)) {
            $file = Storage::disk('local')->get($filePath);

            return response($file, 200)
                ->header('Content-Type', Storage::disk('local')->mimeType($filePath));
        }

        return response()->json(['error' => 'File not found.'], 404);
    }

    /**
     * delete file
     */
    public function destroyFile($id)
    {
        FileRepo::delete($id);
        return ['cleared' => true];
    }
}
