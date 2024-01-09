<?php

use App\Http\Controllers\Admin\Settings\Picklists\GameSources\GameSourcesController;
use Illuminate\Support\Facades\Route;

$controller = GameSourcesController::class;
Route::get('/', [$controller, 'index'])->name('List game sources');
Route::post('/', [$controller, 'store'])->hidden();
