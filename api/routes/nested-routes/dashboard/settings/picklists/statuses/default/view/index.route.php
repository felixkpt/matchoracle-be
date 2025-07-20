<?php

use App\Http\Controllers\Dashboard\Settings\Picklists\Statuses\StatusesController;
use Illuminate\Support\Facades\Route;

$controller = StatusesController::class;
Route::get('/{id}', [$controller, 'show'])->hidden();
Route::put('/{id}', [$controller, 'update'])->hidden();
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->hidden();
