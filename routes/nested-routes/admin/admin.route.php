<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

$controller = AdminController::class;

Route::get('/', [$controller, 'index'])->name('Admin dash')->everyone(true);
Route::get('/stats', [$controller, 'stats'])->name('Site stats')->hidden();
Route::get('/advanced-stats', [$controller, 'advancedStats'])->name('Site advanced stats')->hidden();
