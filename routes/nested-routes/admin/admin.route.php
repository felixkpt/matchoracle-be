<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

$controller = AdminController::class;

Route::get('/', [$controller, 'index'])->name('Main dash')->everyone(true)->public(true);
Route::get('/stats', [$controller, 'stats'])->name('Site stats')->hidden();
