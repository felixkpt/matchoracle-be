<?php

use App\Http\Controllers\Admin\Teams\Coaches\CoachContracts\CoachContractsController;
use Illuminate\Support\Facades\Route;

$controller = CoachContractsController::class;
Route::get('/', [$controller, 'index'])->name('List contracts');
Route::put('/view/{id}', [$controller, 'update']);
Route::get('/view/{id}', [$controller, 'show']);
Route::post('/', [$controller, 'store'])->name('store contract');
