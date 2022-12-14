<?php

use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\CategoryController;
use App\Http\Controllers\api\BoxController;
use App\Http\Controllers\api\ItemController;
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
    Route::get('get_all_users',[UserController::class,'getAllUsers']);

    Route::group(['middleware' => ['jwt.verify']], function() {

        Route::post('logout', [UserController::class,'logout']);

        Route::resource('category', CategoryController::class);

        Route::resource('box', BoxController::class);
        Route::post('box_rename', [BoxController::class,'renameBox']);
        Route::post('box_move', [BoxController::class,'boxMove']);

        Route::resource('item', ItemController::class);
        Route::post('rename_item', [ItemController::class,'renameItem']);

    });

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
