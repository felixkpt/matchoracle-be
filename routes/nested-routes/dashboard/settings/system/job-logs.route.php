<?php

use App\Http\Controllers\Dashboard\Settings\System\LogsController;
use Illuminate\Support\Facades\Route;

$controller = LogsController::class;

Route::get('/', [$controller, 'index'])->name('View Job Logs');
