<?php

use App\Http\Controllers\Dashboard\Teams\Coaches\CoachesController;
use Illuminate\Support\Facades\Route;

$controller = CoachesController::class;
Route::get('/', [$controller, 'index'])->name('List coaches');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store coach');
