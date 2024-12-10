<?php

use App\Http\Controllers\Dashboard\Matches\MatchesController;
use Illuminate\Support\Facades\Route;

// Create an instance of the MatchesController
$controller = MatchesController::class;
$match = '[0-9]+';

// Routes for different match views
Route::get('/', [$controller, 'index'])->name('all matches'); // All matches
Route::get('/today', [$controller, 'today'])->name('today\'s matches'); // Today's matches
Route::get('/yesterday', [$controller, 'yesterday'])->name('yesterday\'s matches'); // Yesterday's matches
Route::get('/tomorrow', [$controller, 'tomorrow'])->name('tomorrow\'s matches'); // Tomorrow's matches
Route::get('/upcoming', [$controller, 'upcoming'])->name('upcoming matches'); // Upcoming matches

// Routes for matches based on year, year/month, and year/month/day
Route::get('/{year}', [$controller, 'year'])->name('year matches')->where(['year' => $match]); // Matches for a specific year
Route::get('/{year}/{month}', [$controller, 'yearMonth'])->name('year_month matches')->where(['year' => $match, 'month' => $match]); // Matches for a specific year and month
Route::get('/{year}/{month}/{day}', [$controller, 'yearMonthDay'])->name('year_month_day matches')->where(['year' => $match, 'month' => $match, 'day' => $match]); // Matches for a specific year, month, and day

// Updated routes for combined date ranges
Route::get('/{start_year}/{start_month}/{start_day}/to/{end_year}/{end_month}/{end_day}', [$controller, 'dateRange'])
    ->where([
        'start_year' => $match, 'start_month' => $match, 'start_day' => $match,
        'end_year' => $match, 'end_month' => $match, 'end_day' => $match
    ])
    ->name('Predictions date_range'); // Predictions for a specified date range

// Store, update, and delete match routes
Route::post('/', [$controller, 'store'])->name('store match'); // Store a new match
Route::put('/', [$controller, 'update'])->name('update match'); // Update an existing match
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy match'); // Delete a specific match
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
