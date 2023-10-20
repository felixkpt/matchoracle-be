<?php

use App\Http\Controllers\Admin\Predictions\View\PredictionController;
use Illuminate\Support\Facades\Route;

$controller = PredictionController::class;
Route::get('/{id}', [$controller, 'show']);
