<?php

use App\Http\Controllers\Dashboard\Settings\System\LogsController;
use Illuminate\Support\Facades\Route;

$controller = LogsController::class;

Route::get('/', [$controller, 'index'])->name('View Job Logs');
Route::get('/seasons', [$controller, 'seasonsJobLogs'])->name('Seasons Job Logs')->hidden();
Route::get('/standings', [$controller, 'standingsJobLogs'])->name('Standings Job Logs')->hidden();
Route::get('/matches', [$controller, 'matchesJobLogs'])->name('Matches Job Logs')->hidden();
Route::get('/match', [$controller, 'matchJobLogs'])->name('Match Job Logs')->hidden();
Route::get('/train-predictions', [$controller, 'trainPredictionsJobLogs'])->name('Train Predictions Job Logs')->hidden();
Route::get('/predictions', [$controller, 'predictionsJobLogs'])->name('Predictions Job Logs')->hidden();
