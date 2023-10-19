<?php

use App\Http\Controllers\Admin\Continents\ContinentsController;
use Illuminate\Support\Facades\Route;

$controller = ContinentsController::class;
Route::get('/{id}', [$controller, 'show'])->name('show');
Route::put('/{id}', [$controller, 'update'])->name('update');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
