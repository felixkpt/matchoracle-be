<?php

use App\Http\Controllers\Dashboard\Countries\CountriesController;
use Illuminate\Support\Facades\Route;

$controller = CountriesController::class;
Route::get('/', [$controller, 'index'])->name('Countries List');
Route::get('/where-has-club-teams', [$controller, 'whereHasClubTeams'])->name('Club Teams')->hidden();
Route::get('/where-has-national-teams', [$controller, 'whereHasNationalTeams'])->name('National Teams')->hidden();

Route::post('/', [$controller, 'store'])->name('store country');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
