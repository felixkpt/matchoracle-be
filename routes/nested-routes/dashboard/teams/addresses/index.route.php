<?php

use App\Http\Controllers\Dashboard\Teams\Addresses\AddressesController;
use Illuminate\Support\Facades\Route;

$controller = AddressesController::class;
Route::get('/', [$controller, 'index'])->name('Addresses List');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store address');
