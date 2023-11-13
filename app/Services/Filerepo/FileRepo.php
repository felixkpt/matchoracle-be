<?php


namespace App\Services\Filerepo;

use App\Services\Filerepo\Models\ModelFile;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use stdClass;

#custom
class  FileRepo
{

    /**
     * Uploads a file.
     *
     * @param Model|null      $record      The associated model record (optional).
     * @param UploadedFile    $file        The file to upload, an UploadedFile object.
     * @param string|null     $folder      The folder path where the file should be saved (optional).
     * @param string|null     $filename    The desired filename for the uploaded file (optional).
     * @param int|null        $update_id   The update ID (optional).
     * @param bool            $public      Indicates whether the file should be publicly accessible (default: true).
     * @param int             $status      The status value (default: 1).
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|object|null
     *         Returns the model or query builder instance if the file was successfully uploaded and saved,
     *         or null if unsuccessful.
     */
    public static function uploadFile(Model|null $record, UploadedFile $file, $folder = null, $filename = null, int $update_id = null, bool $public = true, int $status = 1)
    {

        if (env('FILESYSTEM_DRIVER') == 'gcs') {
            $path = self::saveToGcs($file, $folder, $filename, $public);
        } else {
            $path = self::saveLocally($file, $folder, $filename, $public);
        }

        // save the upload details to files table
        if ($path) {
            return self::insertFile($record, $file, $filename, $path, $update_id, $status);
        } else {
            return null;
        }
    }

    /**
     * Update file record.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $record The file record.
     * @param  mixed  $file The file object to be updated.
     * @param  int  $update_id The update ID.
     * @return void
     */
    public static function updateFileRecord($record, $file, $update_id = null)
    {
        [$model_instance_id, $model_id] = getModelDetails($record);

        // Update the file record with the new model instance ID, model ID, and update ID
        DB::table('model_files')
            ->where('model_files.id', $file->id)
            ->update([
                'model_instance_id' => $model_instance_id,
                'model_id' => $model_id,
                'update_id' => $update_id
            ]);
    }

    /**
     * Save a file to Google Cloud Storage (GCS) and return the file path.
     *
     * @param  mixed  $file The file object to be saved.
     * @param  string|null  $folder The destination folder for the file.
     * @param  string  $filename The desired filename for the file.
     * @param  bool  $public Determine if the file should be publicly accessible.
     * @return string The file path in Google Cloud Storage (GCS).
     */
    private static function saveToGcs($file, $folder, $filename, $public)
    {
        $project_folder = config('app.gcs_project_folder');
        $disk = Storage::disk('gcs');

        if ($folder) {
            $folder = $project_folder . '/' . $folder;
        } else {
            $folder = $project_folder;
        }

        // Remove repeated slashes
        $folder = preg_replace("#/+#", "/", $folder);

        $path = $file->storeAs(
            $folder,
            $filename,
            'gcs'
        );

        // Set the visibility of the file to public
        if ($public) {
            $disk->setVisibility($path, 'public');
        }

        return $path;
    }


    /**
     * Save a file to the local disk and return the file path.
     *
     * @param  mixed  $file The file object to be saved.
     * @param  string|null  $folder The destination folder for the file.
     * @param  string  $filename The desired filename for the file.
     * @param  bool  $public Determine if the file should be publicly accessible.
     * @return string|array The file path in the local disk or an array with an error message if saving fails.
     */
    private static function saveLocally($file, $folder, $filename, $public)
    {
        $project_folder = '';
        $disk = Storage::disk('local');

        if ($folder) {
            $folder = $project_folder . '/' . $folder;
        } else {
            $folder = $project_folder;
        }

        // Remove repeated slashes
        $folder = trim(preg_replace("#/+#", "/", $folder), '/');

        try {
            $new_path = $folder . "/";

            $pre = ($public) ? '/' : 'app/';

            $new_path = $pre . $new_path;

            File::ensureDirectoryExists(storage_path() . '/app/' . $new_path);

            $disk->putFileAs($new_path, $file, $filename);

            return $folder . '/' . $filename;
        } catch (Exception $e) {
            Log::critical("Error saving files to local - \n" . $e->getMessage());
            return [
                'uploaded' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Insert a new file record into the database.
     *
     * @param  mixed  $record The file record.
     * @param  mixed  $file The file object.
     * @param  string  $filename The filename for the file.
     * @param  string  $path The file path.
     * @param  int|null  $update_id The update ID.
     * @param  int  $status The status of the file.
     * @return mixed The inserted file record.
     */
    private static function insertFile($record, $file, $filename, $path, $update_id = null, $status = 1)
    {
        [$model_instance_id, $model_id] = getModelDetails($record);
        Log::alert('FileRepo md:',[ $model_instance_id, $model_id]);

        $originalFileName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $systemFileName = $filename;
        $fileSize = $file->getSize();
        $created_by = auth()->check() ? auth()->user()->id : null;

        if (is_array($path)) {
            Log::critical('FileRepo::', ['message' => $path]);
            return null;
        }

        $modelFile = ModelFile::create([
            'model_instance_id' => $model_instance_id,
            'model_id' => $model_id,
            'name' => $originalFileName,
            'unique_name' => $systemFileName,
            'extension' => $extension,
            'path' => $path,
            'disk' => env('FILESYSTEM_DRIVER', 'local'),
            'size' => $fileSize,
            'update_id' => $update_id,
            'status' => $status,
            'form_token' => null,
            'created_by' => $created_by,
        ]);

        return $modelFile;
    }


    /**
     *
     * get files
     *
     */

    /**
     * Retrieve files associated with a model record.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $record The model record.
     * @param  int|null  $update_id The update ID.
     * @return \Illuminate\Database\Query\Builder The query builder for retrieving files.
     */
    public static function getFiles(Model $record, $update_id = null)
    {
        [$model_instance_id, $model_id] = getModelDetails($record);

        $query = DB::table('model_files')
            ->leftJoin('users', 'model_files.created_by', 'users.id')
            ->where([
                ['model_instance_id', $model_instance_id],
                ['model_id', $model_id]
            ])
            ->select(
                'model_files.id',
                'model_instance_id',
                'model_id',
                'model_files.name',
                'unique_name',
                'extension',
                'path',
                'disk',
                'size',
                'update_id',
                'model_files.status',
                'form_token',
                'model_files.created_at',
                'users.name as uploaded_by'
            );

        if ($update_id) {
            $query->where('update_id', $update_id);
        }

        return $query;
    }

    /**
     * Retrieve temporary files associated with a form token.
     *
     * @return \Illuminate\Database\Query\Builder The query builder for retrieving temporary files.
     */
    public static function getTmpFiles()
    {
        $query = DB::table('model_files')
            ->leftJoin('users', 'model_files.created_by', 'users.id')
            ->where([
                ['form_token', request()->_form_token],
                ['model_files.status', 0]
            ])
            ->select(
                'model_files.id',
                'model_instance_id',
                'model_id',
                'model_files.name',
                'unique_name',
                'extension',
                'path',
                'disk',
                'size',
                'update_id',
                'model_files.status',
                'form_token',
                'model_files.created_at',
                'users.name as uploaded_by'
            );

        return $query;
    }

    /**
     *
     * get files
     *
     */

    /**
     * Retrieve a file by its ID.
     *
     * @param int $id The ID of the file.
     * @return \stdClass|null The file object if found, or aborts with a 404 error.
     */
    public static function getFile($id)
    {
        $query = DB::table('model_files')
            ->leftJoin('users', 'model_files.created_by', 'users.id')
            ->where('model_files.id', $id)
            ->select(
                'model_files.id',
                'model_instance_id',
                'model_id',
                'model_files.name',
                'unique_name',
                'extension',
                'path',
                'disk',
                'size',
                'update_id',
                'model_files.status',
                'form_token',
                'model_files.created_at',
                'users.name as uploaded_by'
            );

        return $query->first() ?? abort(404);
    }

    public static function convertTempFileToUploadedFileInstance($path, $test = true)
    {
        $filesystem = new Filesystem();

        $name = $filesystem->name($path);
        $extension = $filesystem->extension($path);
        $originalName = $name . '.' . $extension;
        $mimeType = null;
        $error = null;
        try {
            $mimeType = $filesystem->mimeType($path);
        } catch (Exception $e) {
            $error = true;
        }

        return new UploadedFile($path, $originalName, $mimeType, $error, $test);
    }

    /**
     * Determine the file type based on the MIME type.
     *
     * @param string $mimeType The MIME type of the file.
     * @return string Returns the file type based on the given MIME type.
     */
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

    public static function download($file)
    {
        if (file_exists($file->path))
            return \response()->download($file->path);

        if ($file->disk == 'gcs')
            return Storage::disk('gcs')->download($file->path);

        if (file_exists(storage_path('app/' . $file->path)))
            return \response()->download(storage_path('app/' . $file->path));
        if (file_exists(storage_path('app/public/' . $file->path)))
            return \response()->download(storage_path('app/public/' . $file->path));

        $slug = Str::slug($file->name);
        $file_path = storage_path('app/' . $file->path . $slug);
        $file_path = str_replace('-' . $file->type, '.' . $file->type, $file_path);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $slug = str_replace($file->type, '.' . $file->type, $slug);
        $file_path = storage_path('app/' . $file->path . $slug);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $file_path = storage_path('app/' . $file->path . $file->name);
        if (file_exists($file_path))
            return \response()->download($file_path);
        $file_path = storage_path('app/' . $file->path . str::slug($file->name));
        return \response()->download($file_path);
    }

    /**
     * Get the URL of a file.
     *
     * @param  mixed  $file The file object.
     * @return string The URL of the file.
     */
    public static function url($file)
    {
        // Check if the filesystem disk is 'gcs'
        if (env('FILESYSTEM_DRIVER') == 'gcs') {
            $path = $file->path;
            if (!Str::startsWith($path, config('app.gcs_project_folder'))) {
                $path = config('app.gcs_project_folder') . '/' . $path;
                // Remove repeated slashes
                $path = preg_replace("#/+#", "/", $path);
            }

            // Return the URL of the file using the appropriate disk
            return Storage::disk($file->disk)->url($path);
        }

        // If the filesystem disk is not 'gcs', construct the URL using the 'storage' path
        return url('storage/' . $file->path);
    }

    /**
     * Delete a file.
     *
     * @param  int  $id The ID of the file.
     * @param  bool  $delete_record Whether to delete the corresponding record from the database (default: true).
     * @return bool True on successful deletion, false otherwise.
     */
    public static function delete($id, $delete_record = true)
    {
        // Get the file using the provided ID
        $file = self::getFile($id);

        // Delete the file from the storage disk
        Storage::disk($file->disk)->delete($file->path);

        // Check if the record deletion is requested
        if ($delete_record === true) {
            // Delete the corresponding record from the 'model_files' table in the database
            return DB::table('model_files')->where('model_files.id', $id)->delete();
        }

        // Return true to indicate successful deletion
        return true;
    }

    public static function storeFacebookFile($file, $customer_id, $record)
    {

        $folder = 'public/facebookv1/send/' . Carbon::now()->format('Y/m/d') . '/' . $customer_id;

        $file_name = Str::random(25) . "." . $file->getClientOriginalExtension();

        $cloud_path = self::uploadFile($record, $file, $folder, $file_name);

        $db_path = $folder . '/' . $file_name;

        return $db_path;
    }


    static function deleteOldTempFiles()
    {
        // delete old temp files
        $files = ModelFile::wheremodel_instance_id(null)
            ->where('form_token', '!=', null)
            ->where('model_files.created_at', '<', Carbon::now()->subDays(1)->format('Y-m-d'))
            ->get();

        foreach ($files as $file) {
            self::delete($file->id);
            // Delete record from DB too
            $file->delete();
        }
    }

    public static function moveFile($file, $model_id, $public = false, $path = null)
    {
        try {
            $original_name = $file->getClientOriginalName();
            $file_type = $file->getClientMimeType();
            $file_size = $file->getSize();
            $arr = explode('.', $original_name);
            $ext = $arr[count($arr) - 1];
            $file_name = str::slug(str_replace($ext, '', $original_name)) . '.' . $ext;

            if ($path)
                $path = $path . '/' . $model_id . '/' . Carbon::now()->format('Y/m/d');
            else
                $path = '/staffs/' . $model_id . '/' . Carbon::now()->format('Y/m/d');

            $new_path = $path . "/";

            if (!$public) {
                File::ensureDirectoryExists(storage_path() . '/app/' . $new_path);
            }

            $new_name = Str::random(3) . '_' . date('H_i_s') . '_' . $file_name;
            $disk = env('FILESYSTEM_DRIVER', 'local');
            if ($public) {
                $disk = 'local';
            }

            Storage::disk($disk)->putFileAs($new_path, $file, $new_name);

            if ($public)
                $pre = 'storage';
            return [
                'file_name' => $original_name,
                'file_size' => $file_size,
                'path' => $path . '/' . $new_name,
                'file_type' => $file_type,
                'uploaded' => true,
                'ext' => $ext,
                'disk' => $disk
            ];
        } catch (\Exception $e) {
            return [
                'uploaded' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create an UploadedFile object from absolute path
     *
     * @param string $path
     * @param bool $test default true
     * @return    object(Illuminate\Http\UploadedFile)
     *
     * Based of Alexandre Thebaldi answer here:
     * https://stackoverflow.com/a/32258317/6411540
     */
    public static function pathToUploadedFile($path, $test = true)
    {
        $filesystem = new Filesystem;

        $name = $filesystem->name($path);
        $extension = $filesystem->extension($path);
        $originalName = $name . '.' . $extension;
        $mimeType = $filesystem->mimeType($path);
        $error = null;

        return new UploadedFile($path, $originalName, $mimeType, $error, $test);
    }

    /**
     * import file to google cloud storage(gcs)
     *
     * usinf filedriver gcs
     *
     * $path = $request->hasFile('file_name') ? self::uploadFile($request->file('file_name'), 'folder_name') : null; // call the trait to upload file anf return path. e.g folder_name/qp7tMBXUuqTUs1roZhd4Hg0Pj.pdf
     *
     */

    public static function base64_to_uploadedfile($base64_string)
    {
        // decode the base64 file
        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_string));

        // save it to temporary dir first.
        $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();

        file_put_contents($tmpFilePath, $fileData);

        // this just to help us get file info.
        $tmpFile = new File($tmpFilePath);

        $file = new UploadedFile(
            $tmpFile->getPathname(),
            $tmpFile->getFilename(),
            $tmpFile->getMimeType(),
            0,
            true // Mark it as test, since the file isn't from real HTTP POST.
        );

        return $file;
    }
}
