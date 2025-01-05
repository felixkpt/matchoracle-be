<?php

use App\Http\Controllers\Dashboard\Competitions\CompetitionsController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionsController::class;
Route::get('/', [$controller, 'index'])->name('Competitions List')->public();
Route::get('/country/{id}', [$controller, 'countryCompetitions'])->name('Country competitions')->public();
Route::get('/results-statistics', [$controller, 'resultsStatistics']); // View statistics for a competitions
Route::get('/prediction-statistics', [$controller, 'predictionStatistics'])->name('Competition predictions stats')->public(); // View prediction statistics for a competitions
Route::post('/', [$controller, 'store'])->name('store');
Route::put('/', [$controller, 'update'])->name('update');
Route::patch('/update-statuses', [$controller, 'updateStatuses'])->hidden(); // Update statuses of multiple records (hidden)
