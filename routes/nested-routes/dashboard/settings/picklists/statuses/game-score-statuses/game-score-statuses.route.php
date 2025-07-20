<?php

use App\Http\Controllers\Dashboard\Settings\Picklists\Statuses\GameScoreStatusController;
use Illuminate\Support\Facades\Route;

$controller = GameScoreStatusController::class;
Route::get('/', [$controller, 'index'])->name('Game Scores Statuses List');
Route::post('/', [$controller, 'store'])->hidden();
