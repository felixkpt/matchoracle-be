<?php

use App\Http\Controllers\Dashboard\Matches\View\MatchController;
use Illuminate\Support\Facades\Route;

$controller = MatchController::class;
Route::get('/{id}', [$controller, 'show']);
Route::put('/{id}', [$controller, 'updateGame'])->name('Update Game');
Route::get('/{id}/head2head', [$controller, 'head2head']);
Route::post('/{id}/vote', [$controller, 'vote']);