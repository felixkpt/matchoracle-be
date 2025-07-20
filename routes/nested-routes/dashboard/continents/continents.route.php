<?php

use App\Http\Controllers\Dashboard\Continents\ContinentsController;
use Illuminate\Support\Facades\Route;

$controller = ContinentsController::class;
Route::get('/', [$controller, 'index'])->name('Continents List');
Route::post('/', [$controller, 'store'])->name('store continent');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
