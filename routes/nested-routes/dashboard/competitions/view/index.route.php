<?php

use App\Http\Controllers\Dashboard\Competitions\View\CompetitionController;
use App\Http\Controllers\Dashboard\Matches\MatchesController;
use App\Http\Controllers\Dashboard\Predictions\PredictionsController;
use Illuminate\Support\Facades\Route;

// Competition routes
$controller = CompetitionController::class;

Route::get('/{id}/fixtures', [$controller, 'fixtures']); // View fixtures for a competition
Route::post('/{id}/fixtures', [$controller, 'getFixtures']); // Get fixtures data for a competition
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']); // View detailed fixtures for a competition
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']); // Get detailed fixtures data for a competition
Route::get('/{id}/update', [$controller, 'update']); // View update page for a competition
Route::post('/{id}/update', [$controller, 'getUpdates']); // Get update data for a competition
Route::post('/{id}/add-sources', [$controller, 'addSources']); // Add sources for a competition
Route::get('/{id}/seasons', [$controller, 'seasons']); // View seasons for a competition
Route::post('/{id}/fetch-seasons', [$controller, 'fetchSeasons']); // Fetch seasons data for a competition
Route::get('/{id}/teams/{season_id?}', [$controller, 'teams']); // View teams for a competition and optional season

Route::get('/{id}/standings/{season_id?}', [$controller, 'standings']); // View standings for a competition and optional season
Route::post('/{id}/fetch-standings/', [$controller, 'fetchStandings']); // Fetch standings data for a competition

Route::post('/{id}/fetch-matches/', [$controller, 'fetchMatches']); // Fetch matches data for a competition

Route::get('/{id}', [$controller, 'show'])->name('show compe'); // Show details of a competition
Route::put('/{id}', [$controller, 'update'])->name('update compe'); // Update details of a competition
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden(); // Update status of a competition (hidden)
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy compe'); // Delete a competition

Route::get('/{id}/odds', [$controller, 'odds']); // View odds for a competition

Route::get('/{id}/statistics', [$controller, 'statistics']); // View statistics for a competition
Route::get('/{id}/prediction-statistics', [$controller, 'predictionStatistics']); // View prediction statistics for a competition
Route::post('/{id}/do-statistics', [$controller, 'doStatistics']); // do stats for a competition

Route::get('/{id}/get-dates-with-unpredicted-games', [$controller, 'getDatesWithUnpredictedGames']); // getDatesWithUnpredictedGames
Route::get('/{id}/tabs', [$controller, 'tabs'])->name('View compe tab counts'); // View tab counts for a competition


// Competition Matches routes
Route::name('matches.')->group(function () {

    $path = '/{competition_id}/matches';
    $controller = MatchesController::class;
    Route::get($path . '/', [$controller, 'index'])->name('list'); // List matches for a competition
    Route::get($path . '/today', [$controller, 'today'])->name('today'); // View today's matches for a competition
    Route::get($path . '/yesterday', [$controller, 'yesterday'])->name('yesterday'); // View yesterday's matches for a competition
    Route::get($path . '/tomorrow', [$controller, 'tomorrow'])->name('tomorrow'); // View tomorrow's matches for a competition
    Route::get($path . '/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']); // View matches for a specific year and competition
    Route::get($path . '/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']); // View matches for a specific year and month and competition
    Route::get($path . '/{year}/{month}/{date}', [$controller, 'yearMonthDay'])->name('year_month_date')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'date' => '[0-9]+']); // View matches for a specific year, month, date, and competition
});

// Competition Prediction routes
Route::name('predictions.')->group(function () {

    $path = '/{competition_id}/predictions';
    $controller = PredictionsController::class;
    Route::get($path . '/', [$controller, 'index'])->name('list'); // List predictions for a competition
    Route::get($path . '/today', [$controller, 'today'])->name('today'); // View today's predictions for a competition
    Route::get($path . '/yesterday', [$controller, 'yesterday'])->name('yesterday'); // View yesterday's predictions for a competition
    Route::get($path . '/tomorrow', [$controller, 'tomorrow'])->name('tomorrow'); // View tomorrow's predictions for a competition
    Route::get($path . '/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']); // View predictions for a specific year and competition
    Route::get($path . '/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']); // View predictions for a specific year and month and competition
    Route::get($path . '/{year}/{month}/{date}', [$controller, 'yearMonthDay'])->name('year_month_date')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'date' => '[0-9]+']); // View predictions for a specific year, month, date, and competition
});
