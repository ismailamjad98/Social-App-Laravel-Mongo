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
    //User Routes
    Route::post('/logout', [UserController::class , 'Logout']);
    Route::get('/profile', [UserController::class , 'profile']); 
    Route::post('/profile/update/{id}', [UserController::class , 'update']);
    // Route::post('/profile/delete/{id}', [UserController::class , 'destroy_User']);
    

    //POST Routes
    Route::post('/post', [PostController::class , 'create']);
    Route::post('post/update/{id}', [PostController::class , 'update']);
    Route::get('post/myposts', [PostController::class , 'myposts']); 
    Route::get('post/allposts', [PostController::class , 'allposts']);
    Route::post('post/delete/{id}', [PostController::class , 'destroy']);

    //Send Friend Request Routes
    Route::post('/send_Request', [Friend_Request::class, 'Send_Friend_Request']);
    Route::post('/my_requests', [Friend_Request::class, 'My_Requests']);
    Route::post('/receive_request', [Friend_Request::class, 'Accept_Request']);
    Route::post('delete_request/{id}', [Friend_Request::class, 'Delete_Request']);

    //Comments Routes
    Route::post('/comment/{id}' , [CommentController::class, 'create']);
    Route::post('/comment/update/{id}' , [CommentController::class, 'update']);
    Route::post('/comment/delete/{id}' , [CommentController::class, 'delete']);
    //comments on friends Post
    Route::post('/friend_post/{id}' , [CommentController::class, 'friend_posts']);

});

