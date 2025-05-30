<?php

use App\Http\Controllers\Dashboard\Settings\RolePermissions\Roles\RolesController;
use Illuminate\Support\Facades\Route;

$controller = RolesController::class;
Route::get('/', [$controller, 'index'])->name('Roles List');
Route::post('/', [$controller, 'store'])->name('Add/Save Role')->hidden();
Route::get('/get-user-roles-and-direct-permissions', [$controller, 'getUserRolesAndDirectPermissions'])->everyone(true)->hidden(true);
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
