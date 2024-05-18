<?php

use App\Http\Controllers\Dashboard\Teams\Venues\VenuesController;
use Illuminate\Support\Facades\Route;

$controller = VenuesController::class;
Route::get('/', [$controller, 'index'])->name('Venues List');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store venue');
