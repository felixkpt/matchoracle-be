<?php

use App\Http\Controllers\Dashboard\Continents\ContinentsController;
use Illuminate\Support\Facades\Route;

$controller = ContinentsController::class;
Route::get('/{id}', [$controller, 'show'])->name('show continents');
Route::put('/{id}', [$controller, 'update'])->name('update continents');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy continent');
