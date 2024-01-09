<?php

use App\Http\Controllers\Admin\Teams\TeamsController;
use Illuminate\Support\Facades\Route;

$controller = TeamsController::class;
Route::get('/', [$controller, 'index'])->name('List teams');
Route::get('/competition/{id}', [$controller, 'index'])->name('List team competitions');
Route::post('/', [$controller, 'store'])->name('store team');
