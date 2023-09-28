<?php

use App\Http\Controllers\Admin\Posts\View\PostController;
use Illuminate\Support\Facades\Route;

$controller = PostController::class;
Route::get('/{id}', [$controller, 'show'])->name('post.show')->everyone(true);
Route::put('/{id}', [$controller, 'update'])->name('post.update');
Route::delete('/{id}', [$controller, 'destroy'])->name('destroy');
