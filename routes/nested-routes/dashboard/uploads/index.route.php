<?php

use App\Services\Filerepo\Controllers\FilesController;
use Illuminate\Support\Facades\Route;

$controller = FilesController::class;
Route::post('/', [$controller, 'uploadFolder'])->name('zipped upload');
