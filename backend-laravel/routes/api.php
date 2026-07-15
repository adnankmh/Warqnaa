<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    MobileApiController,
    MobileGameController,
    MobileSocialController,
    MobileAdminController,
    MobileVoiceController,
    MobilePlatformController,
    MobileAccountController,
    MobileSafetyController,
    MobileModerationController,
    MobileAuthRecoveryController,
    SocialAuthController,
    MobilePushController,
    MobileEngagementController
};

// Backward-compatible public aliases for older Flutter/PWA builds. They
// expose only health, app capability flags and the curated game catalog.
Route::prefix('mobile')->group(function () {
    Route::get('/health', [MobilePlatformController::class, 'legacyHealth']);
    Route::get('/bootstrap', [MobilePlatformController::class, 'legacyBootstrap']);
    Route::get('/games', [MobilePlatformController::class, 'legacyGames']);
});

Route::prefix('mobile/v1')->group(function () {
    Route::get('/health', [MobilePlatformController::class, 'health']);
    Route::get('/app-config', [MobilePlatformController::class, 'config']);
    Route::get('/countries', [MobilePlatformController::class, 'countries']);
    Route::post('/social-auth/start/{provider}', [SocialAuthController::class, 'start'])->middleware('throttle:warqna-auth');
    Route::get('/social-auth/status/{state}', [SocialAuthController::class, 'status'])->middleware('throttle:warqna-auth');
    Route::post('/register', [MobileApiController::class, 'register'])->middleware('throttle:warqna-auth');
    Route::post('/password/forgot', [MobileAuthRecoveryController::class, 'forgot'])->middleware('throttle:warqna-auth');
    Route::post('/password/reset', [MobileAuthRecoveryController::class, 'reset'])->middleware('throttle:warqna-auth');
    Route::post('/login', [MobileApiController::class, 'login'])->middleware('throttle:warqna-auth');
    Route::get('/games/catalog', [MobileGameController::class, 'catalog']);
    Route::get('/games/{gameKey}/rules', [MobileGameController::class, 'rules']);

    Route::middleware(['auth:sanctum', 'throttle:warqna-api', 'supported.app', 'maintenance.guard'])->group(function () {
        Route::get('/bootstrap', [MobileApiController::class, 'bootstrap']);
        Route::post('/email/verification-notification', [MobileAuthRecoveryController::class, 'sendVerification'])->middleware('throttle:warqna-sensitive');
        Route::get('/profile', [MobileApiController::class, 'profile']);
        Route::patch('/profile', [MobileApiController::class, 'updateProfile']);
        Route::get('/wallet', [MobileApiController::class, 'wallet']);
        Route::post('/store/purchase', [MobileApiController::class, 'purchase'])->middleware('throttle:warqna-sensitive');
        Route::post('/store/activate', [MobileApiController::class, 'activateStoreInventoryV183'])->middleware('throttle:warqna-sensitive');
        Route::get('/notifications', [MobileApiController::class, 'notifications']);
        Route::patch('/notifications/{id}/read', [MobileApiController::class, 'markNotification']);
        Route::delete('/notifications/{id}', [MobileApiController::class, 'deleteNotification']);
        Route::post('/push/devices', [MobilePushController::class, 'store']);
        Route::delete('/push/devices', [MobilePushController::class, 'destroy']);
        Route::post('/rewards/daily', [MobileApiController::class, 'claimDaily'])->middleware('throttle:warqna-sensitive');
        Route::post('/rewards/rewarded-ad', [MobileApiController::class, 'claimRewardedAd'])->middleware('throttle:warqna-sensitive');
        Route::get('/engagement/center', [MobileEngagementController::class, 'center']);
        Route::get('/rewards/lucky-wheel', [MobileEngagementController::class, 'luckyWheel']);
        Route::post('/rewards/lucky-wheel/spin', [MobileEngagementController::class, 'spinLuckyWheel'])->middleware('throttle:warqna-sensitive');
        Route::get('/prize-boxes', [MobileEngagementController::class, 'prizeBoxes']);
        Route::post('/prize-boxes/{prizeBox}/open', [MobileEngagementController::class, 'openPrizeBox'])->middleware('throttle:warqna-sensitive');
        Route::post('/packs/daily/open', [MobileEngagementController::class, 'openDailyPack'])->middleware('throttle:warqna-sensitive');
        Route::post('/challenges/{challengeKey}/activate', [MobileEngagementController::class, 'activateChallenge'])->middleware('throttle:warqna-sensitive');
        Route::post('/challenges/{challengeKey}/claim', [MobileEngagementController::class, 'claimChallenge'])->middleware('throttle:warqna-sensitive');
        Route::post('/competitions/{competitionKey}/join', [MobileEngagementController::class, 'joinCompetition'])->middleware('throttle:warqna-sensitive');

        Route::get('/account/export', [MobileAccountController::class, 'export'])->middleware('throttle:warqna-sensitive');
        Route::get('/account/sessions', [MobileAccountController::class, 'sessions']);
        Route::delete('/account/sessions/{tokenId}', [MobileAccountController::class, 'revokeSession'])->middleware('throttle:warqna-sensitive');
        Route::post('/account/deletion-request', [MobileAccountController::class, 'requestDeletion'])->middleware('throttle:warqna-sensitive');
        Route::delete('/account/deletion-request', [MobileAccountController::class, 'cancelDeletion'])->middleware('throttle:warqna-sensitive');
        Route::delete('/account', [MobileAccountController::class, 'requestDeletion'])->middleware('throttle:warqna-sensitive');

        Route::post('/safety/reports', [MobileSafetyController::class, 'report'])->middleware('throttle:warqna-report');
        Route::get('/safety/reports', [MobileSafetyController::class, 'mine']);

        Route::get('/games/{gameKey}/rooms', [MobileGameController::class, 'rooms']);
        Route::post('/games/session', [MobileGameController::class, 'create'])->middleware('throttle:warqna-sensitive');
        Route::post('/games/session/{room:code}/join', [MobileGameController::class, 'join']);
        Route::get('/games/session/{room:code}', [MobileGameController::class, 'show']);
        Route::post('/games/session/{room:code}/action', [MobileGameController::class, 'action']);
        Route::post('/games/session/{room:code}/timeout', [MobileGameController::class, 'timeout']);
        Route::post('/games/session/{room:code}/leave', [MobileGameController::class, 'leave']);
        Route::post('/games/session/{room:code}/kick/{user}', [MobileGameController::class, 'kick'])->middleware('throttle:warqna-sensitive');
        Route::get('/games/session/{room:code}/chat', [MobileGameController::class, 'chat']);
        Route::post('/games/session/{room:code}/chat', [MobileGameController::class, 'sendChat']);

        Route::post('/games/session/{room:code}/voice/join', [MobileVoiceController::class, 'join'])->middleware('throttle:30,1');
        Route::get('/games/session/{room:code}/voice/poll', [MobileVoiceController::class, 'poll'])->middleware('throttle:120,1');
        Route::post('/games/session/{room:code}/voice/signal', [MobileVoiceController::class, 'signal'])->middleware('throttle:240,1');
        Route::patch('/games/session/{room:code}/voice/controls', [MobileVoiceController::class, 'controls'])->middleware('throttle:60,1');
        Route::post('/games/session/{room:code}/voice/leave', [MobileVoiceController::class, 'leave'])->middleware('throttle:30,1');

        Route::get('/social', [MobileSocialController::class, 'index']);
        Route::get('/social/search', [MobileSocialController::class, 'search']);
        Route::get('/social/users/{user}/profile', [MobileSocialController::class, 'profile']);
        Route::post('/social/users/{user}/room-invite', [MobileSocialController::class, 'inviteToRoom']);
        Route::post('/social/room-invite-all', [MobileSocialController::class, 'inviteAllToRoom'])->middleware('throttle:warqna-sensitive');
        Route::post('/social/friends/{user}/request', [MobileSocialController::class, 'request']);
        Route::post('/social/friendships/{friendship}/respond', [MobileSocialController::class, 'respond']);
        Route::delete('/social/friendships/{friendship}', [MobileSocialController::class, 'cancel']);
        Route::post('/social/users/{user}/block', [MobileSocialController::class, 'block']);
        Route::delete('/social/users/{user}/block', [MobileSocialController::class, 'unblock']);
        Route::get('/social/chat/{user}', [MobileSocialController::class, 'thread']);
        Route::post('/social/chat/{user}', [MobileSocialController::class, 'send']);
        Route::post('/social/transfer', [MobileSocialController::class, 'transfer'])->middleware('throttle:warqna-sensitive');

        Route::get('/admin/dashboard', [MobileAdminController::class, 'dashboard']);
        Route::patch('/admin/games/{game}', [MobileAdminController::class, 'updateGame']);
        Route::patch('/admin/store/{item}', [MobileAdminController::class, 'updateStore']);
        Route::post('/admin/users/{user}/action', [MobileAdminController::class, 'userAction']);
        Route::patch('/admin/feature-flags/{flag}', [MobileAdminController::class, 'updateFeatureFlag']);
        Route::post('/admin/releases', [MobileAdminController::class, 'createRelease']);
        Route::get('/admin/designer', [MobileAdminController::class, 'designerIndex']);
        Route::patch('/admin/designer/{entityType}/{key}', [MobileAdminController::class, 'upsertDesigner']);
        Route::delete('/admin/designer/{entity}', [MobileAdminController::class, 'deleteDesigner']);
        Route::get('/admin/moderation/reports', [MobileModerationController::class, 'index']);
        Route::patch('/admin/moderation/reports/{report}', [MobileModerationController::class, 'resolve']);

        Route::post('/logout', [MobileApiController::class, 'logout']);
    });
});
