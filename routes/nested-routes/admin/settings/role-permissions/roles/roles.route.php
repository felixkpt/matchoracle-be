<?php

use App\Http\Controllers\Admin\Settings\RolePermissions\Roles\RolesController;
use Illuminate\Support\Facades\Route;

$controller = RolesController::class;
Route::get('/', [$controller, 'index'])->name('List Roles');
Route::post('/', [$controller, 'store'])->name('Add/Save Role')->hidden();
Route::get('/get-user-roles-and-direct-permissions', [$controller, 'getUserRolesAndDirectPermissions'])->everyone(true)->hidden(true);
