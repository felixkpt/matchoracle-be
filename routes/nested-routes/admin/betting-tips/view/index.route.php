<?php

use App\Http\Controllers\Admin\BettingTips\View\BettingTipController;
use Illuminate\Support\Facades\Route;

$controller = BettingTipController::class;
Route::get('/{id}', [$controller, 'show betting tip']);
