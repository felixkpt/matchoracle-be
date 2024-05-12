<?php

use App\Http\Controllers\Dashboard\BettingTips\BettingTipsController;
use Illuminate\Support\Facades\Route;

Route::post('/', [BettingTipsController::class, 'subscribe'])->public(true)->position(3);