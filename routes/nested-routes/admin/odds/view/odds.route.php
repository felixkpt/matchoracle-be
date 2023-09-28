<?php

use App\Http\Controllers\Admin\Odds\View\OddsController;
use Illuminate\Support\Facades\Route;

Route::name('odds.odds.')->group(function () {
    $controller = OddsController::class;
    Route::get('/{year}/{id}', [$controller, 'index'])->name('index')->where(['year' => '[0-9]+']);
    Route::post('/{year}/{id}', [$controller, 'store'])->name('store')->where(['year' => '[0-9]+']);
    Route::put('/{year}/{id}', [$controller, 'update'])->name('update')->where(['year' => '[0-9]+']);
    Route::delete('/{year}/{id}', [$controller, 'destroy'])->name('destroy')->where(['year' => '[0-9]+']);
});
