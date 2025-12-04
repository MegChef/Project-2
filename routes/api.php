<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FriendController;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// requires token
Route::middleware('auth:sanctum')->group(function () {

    // user actions
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/change-password', [UserController::class, 'changePassword']);
    Route::delete('/delete-self', [UserController::class, 'deleteSelf']);

    // friend actions
    Route::post('/friends/send', [FriendController::class, 'sendRequest']);
    Route::post('/friends/accept', [FriendController::class, 'acceptRequest']);
    Route::post('/friends/reject', [FriendController::class, 'rejectRequest']);
    Route::post('/friends/remove', [FriendController::class, 'removeFriend']);

    Route::get('/friends/pending', [FriendController::class, 'pendingRequests']);
    Route::get('/friends/list', [FriendController::class, 'listFriends']);

    // admin routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::delete('/users/{id}', [UserController::class, 'adminDelete']);
    });

});
