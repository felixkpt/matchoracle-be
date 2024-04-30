<?php

use App\Http\Controllers\Dashboard\Settings\Picklists\Statuses\GameScoreStatusController;
use Illuminate\Support\Facades\Route;

$controller = GameScoreStatusController::class;
Route::get('/', [$controller, 'index'])->name('List Game Scores statuses');
Route::post('/', [$controller, 'store'])->hidden();
