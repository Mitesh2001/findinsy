<?php

use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\CategoryController;
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

Route::group(['as' => 'api.'], function () {

    Route::post('user', [UserController::class,'register']);
    Route::post('send_otp',[UserController::class,'sendOtp']);
    Route::post('login', [UserController::class,'authenticate']);

    Route::group(['middleware' => ['jwt.verify']], function() {

        Route::get('get_all_users',[UserController::class,'getAllUsers']);
        Route::post('logout', [UserController::class,'logout']);

        Route::resource('category', CategoryController::class);

    });

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
