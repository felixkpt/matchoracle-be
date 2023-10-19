<?php

use App\Http\Controllers\Admin\Settings\RolePermissions\Roles\View\RoleController;
use Illuminate\Support\Facades\Route;

$controller = RoleController::class;

Route::any('/{id}/save-permissions', [$controller, 'storeRolePermissions'])->name('Save Role Permissions')->hidden();
Route::any('/{id}/save-menu-and-clean-permissions', [$controller, 'storeRoleMenuAndCleanPermissions'])->hidden();
Route::get('/{id}/get-role-menu', [$controller, 'getRoleMenu'])->everyone(true)->hidden();
Route::get('/{id}/get-user-route-permissions', [$controller, 'getUserRoutePermissions'])->everyone(true)->hidden();

Route::post('/{id}/add-user', [$controller, 'addUser'])->hidden();

Route::get('/{id}', [$controller, 'show'])->hidden();
Route::put('/{id}', [$controller, 'update'])->hidden();
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->hidden();
