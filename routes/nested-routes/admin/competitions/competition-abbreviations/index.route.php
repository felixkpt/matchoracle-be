<?php

use App\Http\Controllers\Admin\Competitions\CompetitionAbbreviations\CompetitionAbbreviationsController;
use Illuminate\Support\Facades\Route;

$controller = CompetitionAbbreviationsController::class;
Route::get('/', [$controller, 'index'])->name('List Competition Abreviations');
Route::post('/', [$controller, 'store'])->name('Store Competition Abreviation')->hidden();
Route::put('/view/{id}', [$controller, 'update'])->name('Update Competition Abreviation')->hidden();
