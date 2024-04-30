<?php

use App\Http\Controllers\Dashboard\Posts\Categories\Topics\TopicsController;
use Illuminate\Support\Facades\Route;

$controller = TopicsController::class;
Route::get('/', [$controller, 'index'])->name('Topics List')->everyone()->hidden();
Route::get('/create', [$controller, 'create'])->name('Create topic')->hidden();
Route::post('/', [$controller, 'store'])->name('Store topic');
Route::put('/{id}', [$controller, 'update'])->name('Update topic');
Route::get('/detail/{id}', [$controller, 'show'])->name('Show topic')->everyone();

Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
