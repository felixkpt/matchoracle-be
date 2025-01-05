<?php

use App\Http\Controllers\Dashboard\Teams\View\TeamController;
use Illuminate\Support\Facades\Route;

$controller = TeamController::class;

Route::get('/{id}/get-games', [$controller, 'getGames'])->public();
Route::get('/{id}', [$controller, 'show'])->name('show team')->public();
Route::get('/{id}/matches', [$controller, 'matches'])->public();
Route::get('/{id}/fixtures', [$controller, 'fixtures'])->public();
Route::get('/{id}/predictions', [$controller, 'predictions'])->public();

Route::put('/{id}', [$controller, 'update'])->name('update team');
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy team');
Route::post('/{id}/update-coach', [$controller, 'updateCoach'])->hidden();

Route::post('/{id}/predictions', [$controller, 'doPredictions']);
Route::post('/{id}/fixtures', [$controller, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']);
Route::post('/{id}/add-sources', [$controller, 'addSources']);
