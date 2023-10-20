<?php

use App\Http\Controllers\Admin\Predictions\View\PredictionController;
use Illuminate\Support\Facades\Route;

$controller = PredictionController::class;
Route::get('/{year}/{id}', [$controller, 'index'])->name('index');
Route::post('/{year}/{id}', [$controller, 'store'])->name('store');
Route::put('/{year}/{id}', [$controller, 'update'])->name('update');
Route::delete('/{year}/{id}', [$controller, 'destroy'])->name('destroy');
