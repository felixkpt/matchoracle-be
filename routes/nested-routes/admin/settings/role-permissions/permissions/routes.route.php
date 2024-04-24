<?php

use App\Http\Controllers\Admin\Settings\RolePermissions\Permissions\RoutesController;
use Illuminate\Support\Facades\Route;

$controller = RoutesController::class;
Route::get('/', [$controller, 'index'])->name('List Routes')->hidden();
Route::post('/', [$controller, 'store'])->name('Store Route')->hidden();
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
