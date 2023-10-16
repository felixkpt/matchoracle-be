<?php

use App\Http\Controllers\Admin\Countries\CountriesController;
use Illuminate\Support\Facades\Route;

$controller = CountriesController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::get('/where-has-club-teams', [$controller, 'whereHasClubTeams'])->name('Club Teams');
Route::get('/where-has-national-teams', [$controller, 'whereHasNationalTeams'])->name('National Teams');

Route::post('/', [$controller, 'store'])->name('store');
