<?php

use App\Http\Controllers\Admin\Competitions\View\CompetitionController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionController::class;

Route::get('/{id}/predictions', [$controller, 'predictions']);
Route::post('/{id}/predictions', [$controller, 'doPredictions']);
Route::get('/{id}/fixtures', [$controller, 'fixtures']);
Route::post('/{id}/fixtures', [$controller, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']);
Route::get('/{id}/update', [$controller, 'update']);
Route::post('/{id}/update', [$controller, 'getUpdates']);
Route::post('/{id}/add-sources', [$controller, 'addSources']);
Route::get('/{id}/seasons', [$controller, 'seasons']);
Route::get('/{id}/teams/{season_id?}', [$controller, 'teams']);

Route::get('/{id}/standings/{season_id?}', [$controller, 'standings']);
Route::post('/{id}/fetch-standings/', [$controller, 'fetchStandings']);

Route::get('/{id}/matches/', [$controller, 'matches']);
Route::post('/{id}/fetch-matches/', [$controller, 'fetchMatches']);

Route::get('/{id}', [$controller, 'show'])->name('show');
Route::put('/{id}', [$controller, 'update'])->name('update');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
