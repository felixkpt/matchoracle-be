<?php

use App\Http\Controllers\Admin\Matchs\View\MatchController;
use Illuminate\Support\Facades\Route;

$controller = MatchController::class;
Route::get('/{id}', [$controller, 'show']);
