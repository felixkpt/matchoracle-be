<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

$controller = DashboardController::class;

Route::get('/', [$controller, 'index'])->name('View Preds Performance');
