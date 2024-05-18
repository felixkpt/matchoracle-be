<?php

use App\Http\Controllers\Dashboard\Settings\RolePermissions\Permissions\RoutesController;
use Illuminate\Support\Facades\Route;

$controller = RoutesController::class;
Route::get('/', [$controller, 'index'])->name('Routes List')->hidden();
Route::post('/', [$controller, 'store'])->name('Store Route')->hidden();
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
