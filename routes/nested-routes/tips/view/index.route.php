<?php

use App\Http\Controllers\Dashboard\BettingTips\BettingTipsController;
use Illuminate\Support\Facades\Route;

$controller = BettingTipsController::class;
Route::get('/{id}', [$controller, 'show'])->public(true);
