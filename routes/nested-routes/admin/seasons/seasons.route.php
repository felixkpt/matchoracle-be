<?php

use App\Http\Controllers\Admin\Seasons\SeasonsController;
use Illuminate\Support\Facades\Route;

$controller = SeasonsController::class;
Route::get('/', [$controller, 'index'])->name('List seasons');
Route::post('/', [$controller, 'store'])->name('store season');
Route::put('/{id}', [$controller, 'update'])->name('update season');
Route::patch('/update-status', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
