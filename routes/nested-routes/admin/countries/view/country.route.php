<?php

use App\Http\Controllers\Admin\Countries\View\CountryController;
use Illuminate\Support\Facades\Route;

Route::name('countries.country.')->group(function () {
    Route::get('/{id}', [CountryController::class, 'index'])->name('index');
    Route::put('/{id}', [CountryController::class, 'update'])->name('update');
    Route::get('/{id}/list-competitions', [CountryController::class, 'listCompetitions'])->name('list_competitions');
    Route::delete('/{id}', [CountryController::class, 'destroy'])->name('destroy');
});
