<?php

use App\Http\Controllers\Dashboard\Settings\Users\UsersController;
use Illuminate\Support\Facades\Route;

$controller = UsersController::class;
Route::get('/', [$controller, 'index'])->name('users list')->icon('mdi:leads');
Route::get('/create', [$controller, 'create'])->name('create user')->icon('prime:bookmark')->hidden(true);
Route::post('/', [$controller, 'store'])->name('store user');
Route::get('/search', [$controller, 'searchUsers']);
Route::get('/emails', [$controller, 'searchEmails']);
Route::get('/emailsSearch', [$controller, 'searchUserEmails']);
Route::get('/export', [$controller, 'exportUsers']);

Route::delete('/delete/{user}', [$controller, 'destroyUser']);
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
