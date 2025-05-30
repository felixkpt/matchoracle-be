<?php

use App\Http\Controllers\Dashboard\Competitions\CompetitionsController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionsController::class;
Route::get('/', [$controller, 'index'])->name('Competitions List');
Route::get('/country/{id}', [$controller, 'countryCompetitions'])->name('Country competitions');
Route::post('/', [$controller, 'store'])->name('store');
Route::put('/', [$controller, 'update'])->name('update');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
