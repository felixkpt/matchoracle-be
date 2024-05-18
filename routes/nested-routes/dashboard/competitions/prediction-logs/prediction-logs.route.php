<?php

use App\Http\Controllers\Dashboard\Competitions\PredictionLogs\PredictionLogsController;
use Illuminate\Support\Facades\Route;

$controller = PredictionLogsController::class;
Route::get('/', [$controller, 'index'])->name('Compe Pred Logs List');
Route::get('/view/{id}', [$controller, 'show'])->hidden();
