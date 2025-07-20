<?php

use App\Http\Controllers\Dashboard\Settings\Users\View\UserController;
use Illuminate\Support\Facades\Route;

$controller = UserController::class;
Route::get('/{id}', [$controller, 'show'])->name('users.user.show')->icon('d');
Route::get('/{id}/edit', [$controller, 'edit'])->name('users.user.edit')->icon('d');
Route::put('/{id}', [$controller, 'update'])->name('users.user.update')->icon('e');
Route::delete('/{id}', [$controller, 'destroy'])->name('users.destroy')->icon('f');

Route::post('token/{id}', [$controller, 'resendToken']);
Route::post('unlock/{id}', [$controller, 'unlockUser']);
Route::post('activate/{id}', [$controller, 'activate']);
Route::post('deactivate/{id}', [$controller, 'deactivate']);
Route::post('auto-login/{id}', [$controller, 'autoLoginUser']);

Route::get('/{id}/activity-log', [$controller, 'userActivityLog']);
Route::get('/{id}/user-activity-log/list', [$controller, 'listUserActivityLogs']);
Route::get('/{id}/failed-logins', [$controller, 'listAttemptedLogins']);
Route::get('/{id}/activity-based-usage', [$controller, 'activityBasedUsage']);
Route::get('/{id}/inactivity-based-usage', [$controller, 'inactivityBasedUsage']);

Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
