<?php

use App\Http\Controllers\Admin\Competitions\CompetitionsController;
use Illuminate\Support\Facades\Route;

Route::name('competitions.')->group(function () {
    Route::get('/', [CompetitionsController::class, 'index'])->name('index');
    Route::post('/', [CompetitionsController::class, 'store'])->name('store');
    Route::get('/list', [CompetitionsController::class, 'list'])->name('list');
    Route::get('/create', [CompetitionsController::class, 'create'])->name('create');
    Route::put('/', [CompetitionsController::class, 'update'])->name('update');
    Route::delete('/{id}', [CompetitionsController::class, 'destroy'])->name('destroy');
});
