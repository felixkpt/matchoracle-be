<?php

use App\Http\Controllers\Dashboard\BettingTips\BettingTipsController;
use Illuminate\Support\Facades\Route;

// Create an instance of the Betting tipsController
$controller = BettingTipsController::class;

// Routes for different prediction views
Route::get('/', [$controller, 'index'])->name('all betting tips')->public(true); // All betting tips
Route::get('/today', [$controller, 'today'])->name('today\'s betting tips')->public(true); // Today's betting tips
Route::get('/yesterday', [$controller, 'yesterday'])->name('yesterday\'s betting tips')->public(true); // Yesterday's betting tips
Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow\'s betting tips')->public(true); // Tomorrow's betting tips

// Routes for betting tips based on year, year/month, and year/month/day
Route::get('/{year}', [$controller, 'year'])->name('year betting tips')->where(['year' => '[0-9]+'])->public(true); // Betting tips for a specific year
Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month betting tips')->where(['year' => '[0-9]+', 'month' => '[0-9]+'])->public(true); // Betting tips for a specific year and month
Route::get('/{year}/{month}/{day}', [$controller, 'yearMonthDay'])->name('year_month_day betting tips')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'day' => '[0-9]+'])->public(true); // Betting tips for a specific year, month, and day

// Updated routes for combined date ranges
Route::get('/{start_year}/{start_month}/{start_day}/to/{end_year}/{end_month}/{end_day}', [$controller, 'dateRange'])
    ->where([
        'start_year' => '[0-9]+', 'start_month' => '[0-9]+', 'start_day' => '[0-9]+',
        'end_year' => '[0-9]+', 'end_month' => '[0-9]+', 'end_day' => '[0-9]+'
    ])
    ->name('Betting tips date_range')->public(true); // Betting tips for a specified date range

Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
