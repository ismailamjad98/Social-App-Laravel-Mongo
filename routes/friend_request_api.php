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

    //Send Friend Request Routes
    Route::post('/send_Request', [Friend_Request::class, 'Send_Friend_Request']);
    Route::post('/my_requests', [Friend_Request::class, 'My_Requests']);
    Route::post('/receive_request', [Friend_Request::class, 'Accept_Request']);
    Route::post('delete_request/{id}', [Friend_Request::class, 'Delete_Request']);

});

