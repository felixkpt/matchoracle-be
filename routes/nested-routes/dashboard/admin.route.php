<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

$controller = DashboardController::class;

Route::get('/', [$controller, 'index'])->name('Main dash')->everyone(true)->public(true);
Route::get('/stats', [$controller, 'stats'])->name('Site stats')->hidden();
