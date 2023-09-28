<?php

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('password', [AuthController::class, 'passwordResetLink']);

Route::get('password/{token}', [AuthController::class, 'getEmail'])->name('getEmail');
Route::post('password-set', [AuthController::class, 'passwordSet'])->name('password.set');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $roles = $user->getRoleNames();
        $user->roles = $roles;
        return ['results' => $user];
    });

    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('abilities', [AuthController::class, 'abilities']);
});
