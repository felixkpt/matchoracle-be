<?php

use App\Http\Controllers\Dashboard\Settings\RolePermissions\Roles\RolesController;
use Illuminate\Support\Facades\Route;

$controller = RolesController::class;
Route::get('/', [$controller, 'index'])->name('List Roles');
Route::post('/', [$controller, 'store'])->name('Add/Save Role')->hidden();
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
