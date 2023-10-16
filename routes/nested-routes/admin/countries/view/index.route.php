<?php

use App\Http\Controllers\Admin\Countries\View\CountryController;
use Illuminate\Support\Facades\Route;

$controller = CountryController::class;
Route::get('/{id}', [$controller, 'show'])->name('show');
Route::put('/{id}', [$controller, 'update'])->name('update');
Route::get('/{id}/list-competitions', [$controller, 'listCompetitions'])->name('list_competitions');
Route::patch('/{id}/status-update', [$controller, 'statusUpdate'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
