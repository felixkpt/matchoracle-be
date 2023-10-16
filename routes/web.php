<?php

use App\Http\Controllers\Admin\AuthTests\AbilitiesController;
use App\Http\Controllers\Admin\CompetitionsController;
use App\Http\Controllers\Admin\FootballDataController;
use App\Http\Controllers\Admin\TeamsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::get('/auth-tests/abilities', [AbilitiesController::class, 'index']);

Route::get('/football-data', [FootballDataController::class, 'index']);
Route::get('/football-data/show', [FootballDataController::class, 'show']);
Route::get('/football-data/find/{id}', [FootballDataController::class, 'findTeamById']);

Route::get('/football-data/competitions/update-or-create/{id}', [CompetitionsController::class, 'updateOrCreate']);
Route::get('/football-data/competitions/find-standings-by-competition/{id}', [CompetitionsController::class, 'findStandingsByCompetition']);

Route::get('/football-data/teams/update-by-competition/{id}', [TeamsController::class, 'updateByCompetition']);