<?php

use App\Http\Controllers\Admin\Countries\CountriesController;
use Illuminate\Support\Facades\Route;

Route::name('countries.')->group(function () {
    Route::get('/', [CountriesController::class, 'index'])->name('index');
    Route::get('/list', [CountriesController::class, 'list'])->name('list');
    Route::get('create', [CountriesController::class, 'create'])->name('create');
    Route::post('/', [CountriesController::class, 'store'])->name('store');
    Route::put('/', [CountriesController::class, 'update'])->name('update');
    Route::get('/{id}', [CountriesController::class, 'show'])->name('show');
    Route::delete('/{id}', [CountriesController::class, 'destroy'])->name('destroy');
});
