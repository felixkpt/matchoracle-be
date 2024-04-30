<?php

use App\Http\Controllers\Dashboard\BettingTips\View\BettingTipController;
use Illuminate\Support\Facades\Route;

$controller = BettingTipController::class;
Route::get('/{id}', [$controller, 'show betting tip']);
