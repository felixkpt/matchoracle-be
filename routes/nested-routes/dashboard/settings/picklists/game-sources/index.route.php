<?php

use App\Http\Controllers\Dashboard\Settings\Picklists\GameSources\GameSourcesController;
use Illuminate\Support\Facades\Route;

$controller = GameSourcesController::class;
Route::get('/', [$controller, 'index'])->name('List game sources');
Route::post('/', [$controller, 'store'])->hidden();
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)