<?php

use App\Http\Controllers\Admin\Teams\Addresses\AddressesController;
use Illuminate\Support\Facades\Route;

$controller = AddressesController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store');
