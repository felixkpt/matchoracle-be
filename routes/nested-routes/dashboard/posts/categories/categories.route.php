<?php

use App\Http\Controllers\Dashboard\Posts\Categories\CategoriesController;
use Illuminate\Support\Facades\Route;

$controller = CategoriesController::class;
Route::get('/', [$controller, 'index'])->name('Categories List')->everyone(true);
Route::get('/create', [$controller, 'create'])->name('Create category')->hidden();
Route::post('/', [$controller, 'store'])->name('Store category');
Route::put('/{id}', [$controller, 'update'])->name('Update category');

Route::get('/{slug}', [$controller, 'show'])
    ->where('slug', '^(?!topics$)[^/]*$')
    ->name('Show Category')
    ->everyone(true);

Route::get('/{slug}/topics', [$controller, 'listCatTopics'])->name('List Cat Topics')->everyone(true);

Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
