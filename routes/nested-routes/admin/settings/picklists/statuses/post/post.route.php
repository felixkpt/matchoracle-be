<?php

use App\Http\Controllers\Admin\Settings\Picklists\Statuses\PostStatusesController;
use Illuminate\Support\Facades\Route;

$controller = PostStatusesController::class;
Route::get('/', [$controller, 'index'])->name('List');
Route::post('/', [$controller, 'store'])->name('Store Post status')->hidden();
Route::put('/{id}', [$controller, 'update'])->name('Update Post status')->hidden();
