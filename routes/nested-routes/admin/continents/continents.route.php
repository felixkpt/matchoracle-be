<?php

use App\Http\Controllers\Admin\Continents\ContinentsController;
use Illuminate\Support\Facades\Route;

$controller = ContinentsController::class;
Route::get('/', [$controller, 'index'])->name('List continents');
Route::post('/', [$controller, 'store'])->name('store continent');
