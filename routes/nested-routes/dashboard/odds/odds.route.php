<?php

use App\Http\Controllers\Dashboard\Odds\OddsController;
use Illuminate\Support\Facades\Route;

$controller = OddsController::class;
$match = '[0-9]+';

Route::get('/', [$controller, 'index'])->name('all odds');
Route::get('/today', [$controller, 'today'])->name('today\'s odds');
Route::get('/yesterday', [$controller, 'yesterday'])->name('yesterday\'s odds');
Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow\'s odds');
Route::get('/upcoming', [$controller, 'upcoming'])->name('upcoming odds'); // Upcoming odds

Route::get('/{year}', [$controller, 'year'])->name('year odds')->where(['year' => $match]);
Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month odds')->where(['year' => $match, 'month' => $match]);
Route::get('/{year}/{month}/{day}', [$controller, 'yearMonthDay'])->name('year_month_day odds')->where(['year' => $match, 'month' => $match, 'day' => $match]);

// Updated routes for combined date ranges
Route::get('/{start_year}/{start_month}/{start_day}/to/{end_year}/{end_month}/{end_day}', [$controller, 'dateRange'])
    ->where([
        'start_year' => $match, 'start_month' => $match, 'start_day' => $match,
        'end_year' => $match, 'end_month' => $match, 'end_day' => $match
    ])
    ->name('Odds date_range'); // Odds for a specified date range

Route::post('/', [$controller, 'store'])->name('store odds');
Route::put('/', [$controller, 'update'])->name('update odds');
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy odds');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
