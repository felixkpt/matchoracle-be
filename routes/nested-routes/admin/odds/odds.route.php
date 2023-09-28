<?php

use App\Http\Controllers\Admin\Odds\OddsController;
use Illuminate\Support\Facades\Route;

Route::name('odds.')->group(function () {
    $controller = OddsController::class;
    Route::get('/', [$controller, 'index'])->name('today');
    Route::get('/today', [$controller, 'index'])->name('today');
    Route::get('/yesterday', [$controller, 'index'])->name('yesterday');
    Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow');
    Route::get('/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']);
    Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']);
    Route::get('/{year}/{month}/{date}', [$controller, 'yearMonthDate'])->name('year_month_date')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'date' => '[0-9]+']);
    Route::post('/', [$controller, 'store'])->name('store');
    Route::put('/', [$controller, 'update'])->name('update');
    Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
});
