<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AiAssistantController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DealController;
use App\Http\Controllers\Api\V1\FlutterwaveController;
use App\Http\Controllers\Api\V1\MarketProxyController;
use App\Http\Controllers\Api\V1\ModuleController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\RewardsController;
use App\Http\Controllers\Api\V1\SavedLocationController;
use App\Http\Controllers\Api\V1\SecurityController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\MiniAppController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| OPOOBO Super App API
| Base URL: /api/v1
|
*/

Route::prefix('v1')->group(function () {

    // Public routes (no auth required)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // Store — public browse + submit
    Route::get('/store', [StoreController::class, 'index']);
    Route::post('/store/submit', [StoreController::class, 'submit']);
    Route::post('/store/install/{id}', [StoreController::class, 'install']);
    Route::get('/store/{name}/version', [StoreController::class, 'versionCheck']);

    // Mini-app detail — public
    Route::get('/mini-apps/{id}', [MiniAppController::class, 'show']);

    // Protected routes (Keycloak JWT auth required)
    Route::middleware('auth:keycloak')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // User Profile
        Route::get('/user/profile', [UserController::class, 'profile']);
        Route::put('/user/profile', [UserController::class, 'updateProfile']);

        // Dashboard Stats
        Route::get('/user/stats', [StatsController::class, 'index']);

        // AI Assistant
        Route::post('/ai/chat', [AiAssistantController::class, 'chat']);
        Route::post('/ai/scan', [AiAssistantController::class, 'scan']);

        // Placeholder catalogue. Redemption and point accrual are not implemented.
        Route::get('/deals', [DealController::class, 'index']);
        Route::get('/rewards', [RewardsController::class, 'index']);

        // Market Proxy (public) — accept GET + POST so mobile
        // clients can use either verb; proxy forwards correctly upstream.
        Route::match(['GET', 'POST'], '/market/home', [MarketProxyController::class, 'home']);
        Route::match(['GET', 'POST'], '/market/items', [MarketProxyController::class, 'items']);
        Route::match(['GET', 'POST'], '/market/items/{slug}', [MarketProxyController::class, 'itemDetail']);
        Route::match(['GET', 'POST'], '/market/categories', [MarketProxyController::class, 'categories']);
        Route::match(['GET', 'POST'], '/market/reels', [MarketProxyController::class, 'reels']);
        Route::match(['GET', 'POST'], '/market/jobs', [MarketProxyController::class, 'jobs']);
        Route::match(['GET', 'POST'], '/market/search', [MarketProxyController::class, 'search']);

        // Market Proxy (auth-required)
        Route::match(['GET', 'POST'], '/market/notifications', [MarketProxyController::class, 'notifications']);
        Route::match(['GET', 'POST'], '/market/my-items', [MarketProxyController::class, 'myItems']);
        Route::post('/market/post-item', [MarketProxyController::class, 'postItem']);
        Route::post('/market/payment-intent', [MarketProxyController::class, 'paymentIntent']);
        Route::match(['GET', 'POST'], '/market/chat-list', [MarketProxyController::class, 'chatList']);
        Route::match(['GET', 'POST'], '/market/chat-messages', [MarketProxyController::class, 'chatMessages']);
        Route::match(['GET', 'POST'], '/market/send-message', [MarketProxyController::class, 'sendMessage']);
        Route::match(['GET', 'POST'], '/market/item-offer', [MarketProxyController::class, 'itemOffer']);
        Route::match(['GET', 'POST'], '/market/offer-list', [MarketProxyController::class, 'offerList']);
        Route::post('/market/job-apply', [MarketProxyController::class, 'jobApply']);
        Route::match(['GET', 'POST'], '/market/favourite', [MarketProxyController::class, 'favourite']);
        Route::match(['GET', 'POST'], '/market/favourites', [MarketProxyController::class, 'favourites']);
        Route::match(['GET', 'POST'], '/market/report-reasons', [MarketProxyController::class, 'reportReasons']);
        Route::match(['GET', 'POST'], '/market/report-item', [MarketProxyController::class, 'reportItem']);
        Route::match(['GET', 'POST'], '/market/submit-review', [MarketProxyController::class, 'submitReview']);

        // Module Linking
        Route::post('/modules/auto-link', [ModuleController::class, 'autoLink']);
        Route::get('/modules', [ModuleController::class, 'index']);
        Route::post('/modules/check-email', [ModuleController::class, 'checkEmail']);
        Route::post('/modules/link', [ModuleController::class, 'link']);
        Route::post('/modules/create-account', [ModuleController::class, 'createAccount']);
        Route::post('/modules/unlink', [ModuleController::class, 'unlink']);
        Route::put('/modules/update-password', [ModuleController::class, 'updatePassword']);

        // Payment Methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
        Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
        Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
        Route::put('/payment-methods/{id}/default', [PaymentMethodController::class, 'setDefault']);

        // Addresses
        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{id}', [AddressController::class, 'update']);
        Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

        // Saved Locations
        Route::get('/saved-locations', [SavedLocationController::class, 'index']);
        Route::post('/saved-locations', [SavedLocationController::class, 'store']);
        Route::delete('/saved-locations/{id}', [SavedLocationController::class, 'destroy']);

        // Security
        Route::get('/security/login-history', [SecurityController::class, 'loginHistory']);
        Route::delete('/security/sessions/{id}', [SecurityController::class, 'revokeSession']);
        Route::post('/security/record-login', [SecurityController::class, 'recordLogin']);

        // Flutterwave Saved Authorizations
        Route::get('/flutterwave/authorizations', [FlutterwaveController::class, 'index']);
        Route::post('/flutterwave/authorizations', [FlutterwaveController::class, 'storeAuthorization']);
        Route::delete('/flutterwave/authorizations/{id}', [FlutterwaveController::class, 'destroy']);
        Route::post('/flutterwave/charge-saved', [FlutterwaveController::class, 'chargeSaved']);
        Route::get('/flutterwave/verify/{txRef}', [FlutterwaveController::class, 'verifyTransaction']);

        // Mini-app review (admin)
        Route::post('/mini-apps/{id}/review', [MiniAppController::class, 'review']);
        Route::post('/mini-apps/{id}/admin-test', [MiniAppController::class, 'adminTest']);
        Route::post('/mini-apps/preflight', [MiniAppController::class, 'preflight']);
    });
});
