<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::middleware('throttle:login')->post('/v1/login', [AuthController::class, 'login'])->name('api.v1.login');
// Route::post('/v1/register', [AuthController::class, 'register'])->name('api.v1.register');
// Route::middleware('auth:sanctum')->post('/v1/logout', [AuthController::class, 'logout'])->name('api.v1.logout');



Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {
    Route::controller(AuthController::class)->group(
        function () {
            Route::post('/login', 'login')->middleware('throttle:login')->name('login');
            Route::post('/register', 'register')->name('register');
            Route::post('/logout', 'logout')->middleware('auth:sanctum')->name('logout');
        }
    );
});
