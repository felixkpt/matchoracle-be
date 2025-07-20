<?php

use App\Http\Controllers\Dashboard\Teams\TeamsController;
use Illuminate\Support\Facades\Route;

$controller = TeamsController::class;
Route::get('/', [$controller, 'index'])->name('Teams List');
Route::get('/competition/{id}', [$controller, 'index'])->name('Team Competitions  List');
Route::post('/', [$controller, 'store'])->name('store team');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
