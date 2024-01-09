<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;

$controller = AdminController::class;

Route::get('/', [$controller, 'index'])->name('View Preds Performance');
