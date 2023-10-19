<?php

use App\Http\Controllers\Admin\Settings\Picklists\GameSources\GameSourcesController;
use Illuminate\Support\Facades\Route;

$controller = GameSourcesController::class;
Route::get('/{id}', [$controller, 'show'])->hidden();
Route::put('/{id}', [$controller, 'update'])->hidden();
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->hidden();
