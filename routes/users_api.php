<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Friend_Request;


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

//User Routes
Route::post('/register', [UserController::class , 'register']);
Route::post('/login', [UserController::class , 'login']);
Route::get('emailVerify/{token}/{email}', [UserController::class , 'EmailVerify']);

//User Routes with middleware
Route::middleware(['token'])->group(function () {
    Route::post('/logout', [UserController::class , 'Logout']);
    Route::get('/profile', [UserController::class , 'profile']); 
    Route::post('/profile/update/{id}', [UserController::class , 'update']);
    // Route::post('/profile/delete/{id}', [UserController::class , 'destroy_User']);
});

