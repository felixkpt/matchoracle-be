<?php

use App\Http\Controllers\Dashboard\Predictions\View\PredictionController;
use Illuminate\Support\Facades\Route;

$controller = PredictionController::class;
Route::get('/{id}', [$controller, 'show']);
