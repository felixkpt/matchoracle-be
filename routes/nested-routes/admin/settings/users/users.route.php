<?php

use App\Http\Controllers\Admin\Settings\Users\UsersController;
use Illuminate\Support\Facades\Route;

$controller = UsersController::class;
Route::get('/', [$controller, 'index'])->name('users.index')->icon('mdi:leads');
Route::get('/create', [$controller, 'create'])->name('users.create')->icon('prime:bookmark')->hidden(true);
Route::post('/', [$controller, 'store'])->name('users.store')->icon('c');

Route::get('/search', [$controller, 'searchUsers']);
Route::get('/emails', [$controller, 'searchEmails']);
Route::get('/emailsSearch', [$controller, 'searchUserEmails']);

Route::delete('/delete/{user}', [$controller, 'destroyUser']);

Route::get('/export', [$controller, 'exportUsers']);
