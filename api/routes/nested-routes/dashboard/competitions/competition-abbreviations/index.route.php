<?php

use App\Http\Controllers\Dashboard\Competitions\CompetitionAbbreviations\CompetitionAbbreviationsController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionAbbreviationsController::class;
Route::get('/', [$controller, 'index'])->name('Compe Abbrvs List');
Route::post('/', [$controller, 'store'])->name('Store Compe Abbrv')->hidden();
Route::put('/view/{id}', [$controller, 'update'])->name('Update Compe Abbrv')->hidden();
