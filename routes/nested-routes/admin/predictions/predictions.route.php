<?php

use App\Http\Controllers\Admin\Predictions\PredictionsController;
use Illuminate\Support\Facades\Route;

$controller = PredictionsController::class;
Route::get('/', [$controller, 'index'])->name('today');
Route::get('/today', [$controller, 'index'])->name('today');
Route::get('/yesterday', [$controller, 'index'])->name('yesterday');
Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow');
Route::get('/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']);
Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']);
Route::get('/{year}/{month}/{day}', [$controller, 'yearMonthDay'])->name('year_month_day')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'day' => '[0-9]+']);

Route::post('/posting-from-python-app', [$controller, 'storeFromPythonApp']);
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
