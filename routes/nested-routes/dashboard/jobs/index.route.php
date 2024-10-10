<?php

use App\Http\Controllers\Dashboard\Jobs\JobsController;
use Illuminate\Support\Facades\Route;

$controller = JobsController::class;
Route::patch('/{id}/update-status', [$controller, 'updateStatus'])->hidden();
