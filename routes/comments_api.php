<?php

use App\Http\Controllers\CommentController;
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

Route::middleware(['token'])->group(function () {

    //Comments Routes
    Route::post('/comment/{id}' , [CommentController::class, 'create']);
    Route::post('/comment/update/{p_id}/{c_id}' , [CommentController::class, 'update']);
    Route::post('/comment/delete/{p_id}/{c_id}' , [CommentController::class, 'delete']);
    //comments on friends Post
    Route::post('/friend_post/{id}' , [CommentController::class, 'friend_posts']);

});

