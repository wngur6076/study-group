<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\StudyRequestController;
use App\Http\Controllers\PostStudyRequestsController;

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

Route::prefix('/auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signup']);

    Route::middleware('auth:api')->group(function() {
        Route::get('logout', [AuthController::class, 'logout']);
        Route::get('users', [AuthController::class, 'user']);
    });
});

Route::middleware('auth:api')->group(function() {
    Route::post('/study-groups/{id}/request', [StudyRequestController::class, 'store']);
    Route::get('/study-groups/{id}/request', [StudyRequestController::class, 'index']);
    Route::apiResources([
        '/posts' => PostsController::class,
    ]);
});
