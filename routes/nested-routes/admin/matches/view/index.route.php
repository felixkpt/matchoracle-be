<?php

use App\Http\Controllers\Admin\Matches\View\MatchController;
use Illuminate\Support\Facades\Route;

$controller = MatchController::class;
Route::get('/{id}', [$controller, 'show']);
Route::get('/{id}/head2head', [$controller, 'head2head']);
Route::post('/{id}/vote', [$controller, 'vote']);
Route::post('/{id}/update-game', [$controller, 'updateGame'])->name('Update Game');