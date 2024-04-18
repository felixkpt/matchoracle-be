<?php

use App\Http\Controllers\Auth\AuthController;
use App\Models\User;
use App\Services\NestedRoutes\RoutesHelper;
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
Route::get('test', function() {
    $nestedRoutes = (new RoutesHelper())->getRoutes('');
    dd($nestedRoutes);

    foreach (collect(Route::getRoutes())->pluck('uri') as $r) {
        echo ($r) . "<br>";
    }
    dd();

});
