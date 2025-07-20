<?php
// Files routes

use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Support\Facades\Route;

$controller = FilesController::class;

// Prefix all generated routes
$prefix = 'api/admin';

// Middlewares to be passed before accessing any route
$middleWares = ['api'];

Route::middleware(array_filter($middleWares))
    ->prefix($prefix)
    ->group(function () use ($controller) {

        $middleWares = [];
        $middleWares[] = 'auth:sanctum';
        $middleWares[] = 'nested_routes_auth';

        Route::middleware(array_filter($middleWares))
            ->group(function () use ($controller) {
                Route::post('file-repo/tmp', [$controller, 'storeFile']);
                Route::post('file-repo/tmp/delete/{id}', [$controller, 'destroyFile']);
                Route::post('file-repo/add-files', [$controller, 'addImages']);
                Route::post('file-repo/image/delete/{id}', [$controller, 'deleteImage']);
                Route::post('file-repo/upload-image', [$controller, 'uploadImage']);
            });

        // overide the above middlewares if request token is present
        if (request()->token) {
            $middleWares = ['temporary_token'];
        }

        Route::middleware(array_filter($middleWares))
            ->group(function () use ($controller) {
                Route::get('file-repo/{path}', [$controller, 'show'])->where('path', '.*')->name('file.show');
            });
    });
