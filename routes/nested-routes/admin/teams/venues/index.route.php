<?php

use App\Http\Controllers\Admin\Teams\Venues\VenuesController;
use Illuminate\Support\Facades\Route;

$controller = VenuesController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store');
