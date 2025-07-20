<?php

use App\Http\Controllers\Dashboard\AutomationReportController;
use Illuminate\Support\Facades\Route;

$controller = AutomationReportController::class;

Route::get('/', [$controller, 'index'])->name('Automation report');
