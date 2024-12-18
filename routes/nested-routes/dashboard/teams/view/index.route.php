<?php

use App\Http\Controllers\Dashboard\Teams\View\TeamController;
use Illuminate\Support\Facades\Route;

$controller = TeamController::class;

Route::get('/{id}/predictions', [$controller, 'predictions']);
Route::post('/{id}/predictions', [$controller, 'doPredictions']);
Route::get('/{id}/fixtures', [$controller, 'fixtures']);
Route::post('/{id}/fixtures', [$controller, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']);
Route::post('/{id}/add-sources', [$controller, 'addSources']);

Route::get('/{id}/get-games', [$controller, 'getGames']);
Route::get('/{id}/matches', [$controller, 'matches']);
Route::get('/{id}/predictions', [$controller, 'predictions']);

Route::post('/{id}/update-coach', [$controller, 'updateCoach'])->hidden();

Route::get('/{id}', [$controller, 'show'])->name('show team');
Route::put('/{id}', [$controller, 'update'])->name('update team');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy team');
