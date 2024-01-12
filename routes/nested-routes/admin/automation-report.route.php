<?php

use App\Http\Controllers\Admin\AutomationReportController;
use Illuminate\Support\Facades\Route;

$controller = AutomationReportController::class;

Route::get('/', [$controller, 'index'])->name('Automation report');
