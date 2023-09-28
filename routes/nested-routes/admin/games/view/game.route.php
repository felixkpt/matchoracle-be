<?php

use App\Http\Controllers\Admin\Games\View\GameController;
use Illuminate\Support\Facades\Route;

Route::name('games.game.')->group(function () {
    $controller = GameController::class;
    Route::get('/{year}/{id}', [$controller, 'index'])->name('index');
    Route::post('/{year}/{id}', [$controller, 'store'])->name('store');
    Route::put('/{year}/{id}', [$controller, 'update'])->name('update');
    Route::delete('/{year}/{id}', [$controller, 'destroy'])->name('destroy');
});
