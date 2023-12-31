<?php

use App\Http\Controllers\Admin\Posts\PostsController;
use Illuminate\Support\Facades\Route;

$controller = PostsController::class;
Route::get('/', [$controller, 'index'])->name('Posts List')->everyone(true);
Route::get('/create', [$controller, 'create'])->name('post.create');
Route::post('/', [$controller, 'store'])->name('post.store');
