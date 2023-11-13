<?php

use App\Http\Controllers\Admin\Settings\RolePermissions\Permissions\View\PermissionController;
use Illuminate\Support\Facades\Route;

$controller = PermissionController::class;
Route::put('/{id}', [$controller, 'update'])->hidden();
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
