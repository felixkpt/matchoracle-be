<?php

use App\Http\Controllers\Dashboard\Settings\Picklists\BettingStrategies\BettingStrategiesProConsContoller;
use Illuminate\Support\Facades\Route;

$controller = BettingStrategiesProConsContoller::class;
Route::get('/', [$controller, 'index'])->name('List Post statuses');
Route::post('/', [$controller, 'store'])->name('Store Post status')->hidden();
Route::put('/{id}', [$controller, 'update'])->name('Update Post status')->hidden();
