<?php

use App\Http\Controllers\Dashboard\Countries\View\CountryController;
use Illuminate\Support\Facades\Route;

$controller = CountryController::class;
Route::get('/{id}', [$controller, 'show'])->name('show countries');
Route::put('/{id}', [$controller, 'update'])->name('update countries');
Route::get('/{id}/list-competitions', [$controller, 'listCompetitions'])->name('list_competitions');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy country');
