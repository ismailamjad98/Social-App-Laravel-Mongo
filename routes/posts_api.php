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

Route::middleware(['token'])->group(function () {

    //POST Routes
    Route::post('/post', [PostController::class , 'create']);
    Route::post('post/update/{id}', [PostController::class , 'update']);
    Route::get('post/myposts', [PostController::class , 'myposts']); 
    Route::get('post/allposts', [PostController::class , 'allposts']);
    Route::post('post/delete/{id}', [PostController::class , 'destroy']);

});

