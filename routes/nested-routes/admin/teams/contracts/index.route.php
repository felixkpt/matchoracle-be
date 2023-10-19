<?php

use App\Http\Controllers\Admin\Teams\CoachContracts\CoachContractsController;
use Illuminate\Support\Facades\Route;

$controller = CoachContractsController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store');
