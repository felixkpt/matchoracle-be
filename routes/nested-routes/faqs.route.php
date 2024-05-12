<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'faqs'])->public(true)->position(3);