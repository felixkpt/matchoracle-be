<?php

use App\Http\Controllers\Admin\Seasons\SeasonsController;
use Illuminate\Support\Facades\Route;

$controller = SeasonsController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::post('/', [$controller, 'store'])->name('store');
Route::put('/{id}', [$controller, 'update'])->name('update');
