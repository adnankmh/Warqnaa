<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    MobileApiController,
    MobileGameController,
    MobileSocialController,
    MobileAdminController
};

Route::prefix('mobile/v1')->group(function () {
    Route::post('/register', [MobileApiController::class, 'register']);
    Route::post('/login', [MobileApiController::class, 'login']);
    Route::get('/games/catalog', [MobileGameController::class, 'catalog']);
    Route::get('/games/{gameKey}/rules', [MobileGameController::class, 'rules']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/bootstrap', [MobileApiController::class, 'bootstrap']);
        Route::get('/profile', [MobileApiController::class, 'profile']);
        Route::patch('/profile', [MobileApiController::class, 'updateProfile']);
        Route::get('/wallet', [MobileApiController::class, 'wallet']);
        Route::post('/store/purchase', [MobileApiController::class, 'purchase']);
        Route::get('/notifications', [MobileApiController::class, 'notifications']);
        Route::patch('/notifications/{id}/read', [MobileApiController::class, 'markNotification']);
        Route::delete('/notifications/{id}', [MobileApiController::class, 'deleteNotification']);
        Route::post('/rewards/daily', [MobileApiController::class, 'claimDaily']);

        Route::post('/games/session', [MobileGameController::class, 'create']);
        Route::get('/games/session/{room:code}', [MobileGameController::class, 'show']);
        Route::post('/games/session/{room:code}/action', [MobileGameController::class, 'action']);
        Route::post('/games/session/{room:code}/timeout', [MobileGameController::class, 'timeout']);
        Route::post('/games/session/{room:code}/leave', [MobileGameController::class, 'leave']);
        Route::get('/games/session/{room:code}/chat', [MobileGameController::class, 'chat']);
        Route::post('/games/session/{room:code}/chat', [MobileGameController::class, 'sendChat']);

        Route::get('/social', [MobileSocialController::class, 'index']);
        Route::get('/social/search', [MobileSocialController::class, 'search']);
        Route::post('/social/friends/{user}/request', [MobileSocialController::class, 'request']);
        Route::post('/social/friendships/{friendship}/respond', [MobileSocialController::class, 'respond']);
        Route::delete('/social/friendships/{friendship}', [MobileSocialController::class, 'cancel']);
        Route::post('/social/users/{user}/block', [MobileSocialController::class, 'block']);
        Route::delete('/social/users/{user}/block', [MobileSocialController::class, 'unblock']);
        Route::get('/social/chat/{user}', [MobileSocialController::class, 'thread']);
        Route::post('/social/chat/{user}', [MobileSocialController::class, 'send']);
        Route::post('/social/transfer', [MobileSocialController::class, 'transfer']);

        Route::get('/admin/dashboard', [MobileAdminController::class, 'dashboard']);
        Route::patch('/admin/games/{game}', [MobileAdminController::class, 'updateGame']);
        Route::patch('/admin/store/{item}', [MobileAdminController::class, 'updateStore']);
        Route::post('/admin/users/{user}/action', [MobileAdminController::class, 'userAction']);

        Route::post('/logout', [MobileApiController::class, 'logout']);
    });
});
