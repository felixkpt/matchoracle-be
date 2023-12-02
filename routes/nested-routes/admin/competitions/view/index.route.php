<?php

use App\Http\Controllers\Admin\Competitions\View\CompetitionController;
use App\Http\Controllers\Admin\Matches\MatchesController;
use App\Http\Controllers\Admin\Predictions\PredictionsController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionController::class;

Route::get('/{id}/fixtures', [$controller, 'fixtures']);
Route::post('/{id}/fixtures', [$controller, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']);
Route::get('/{id}/update', [$controller, 'update']);
Route::post('/{id}/update', [$controller, 'getUpdates']);
Route::post('/{id}/add-sources', [$controller, 'addSources']);
Route::get('/{id}/seasons', [$controller, 'seasons']);
Route::post('/{id}/fetch-seasons', [$controller, 'fetchSeasons']);
Route::get('/{id}/teams/{season_id?}', [$controller, 'teams']);

Route::get('/{id}/standings/{season_id?}', [$controller, 'standings']);
Route::post('/{id}/fetch-standings/', [$controller, 'fetchStandings']);

Route::post('/{id}/fetch-matches/', [$controller, 'fetchMatches']);

Route::get('/{id}', [$controller, 'show'])->name('show');
Route::put('/{id}', [$controller, 'update'])->name('update');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');

Route::get('/{id}/statistics', [$controller, 'statistics']);

// Competition Matche routes
Route::name('matches.')->group(function () {

    $path = '/{competition_id}/matches';
    $controller = MatchesController::class;
    Route::get($path . '/', [$controller, 'index'])->name('list');
    Route::get($path . '/today', [$controller, 'today'])->name('today');
    Route::get($path . '/yesterday', [$controller, 'yesterday'])->name('yesterday');
    Route::get($path . '/tomorrow', [$controller, 'tomorrow'])->name('tomorrow');
    Route::get($path . '/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']);
    Route::get($path . '/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']);
    Route::get($path . '/{year}/{month}/{date}', [$controller, 'yearMonthDay'])->name('year_month_date')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'date' => '[0-9]+']);
});

// Competition Prediction routes
Route::name('predictions.')->group(function () {

    $path = '/{competition_id}/predictions';
    $controller = PredictionsController::class;
    Route::get($path . '/', [$controller, 'index'])->name('list');
    Route::get($path . '/today', [$controller, 'today'])->name('today');
    Route::get($path . '/yesterday', [$controller, 'yesterday'])->name('yesterday');
    Route::get($path . '/tomorrow', [$controller, 'tomorrow'])->name('tomorrow');
    Route::get($path . '/{year}', [$controller, 'year'])->name('year')->where(['year' => '[0-9]+']);
    Route::get($path . '/{year}/{month}', [$controller, 'yearMonth'])->name('year_month')->where(['year' => '[0-9]+', 'month' => '[0-9]+']);
    Route::get($path . '/{year}/{month}/{date}', [$controller, 'yearMonthDay'])->name('year_month_date')->where(['year' => '[0-9]+', 'month' => '[0-9]+', 'date' => '[0-9]+']);
});

