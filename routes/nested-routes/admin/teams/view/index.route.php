<?php

use App\Http\Controllers\Admin\Teams\View\TeamController;
use Illuminate\Support\Facades\Route;

$controller = TeamController::class;
Route::get('/{id}', [$controller, 'show']);
Route::post('/{id}', [$controller, 'store']);

Route::get('/{id}/predictions', [$controller, 'predictions']);
Route::post('/{id}/predictions', [$controller, 'doPredictions']);
Route::get('/{id}/fixtures', [$controller, 'fixtures']);
Route::post('/{id}/fixtures', [$controller, 'getFixtures']);
Route::get('/{id}/detailed-fixtures', [$controller, 'detailedFixtures']);
Route::post('/{id}/detailed-fixtures', [$controller, 'getDetailedFixtures']);
Route::get('/{id}/update', [$controller, 'update']);
Route::post('/{id}/update', [$controller, 'getUpdates']);
Route::post('/{id}/add-sources', [$controller, 'addSources']);

Route::get('/{id}/get-games', [$controller, 'getGames']);

Route::post('/{id}/change-status', [$controller, 'changeStatus']);
