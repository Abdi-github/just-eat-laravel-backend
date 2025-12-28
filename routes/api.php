<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CuisineController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ── Public routes (no authentication required) ────────────────────────────────
Route::prefix('public')->group(function () {

    // Locations
    Route::prefix('locations')->group(function () {
        Route::get('cantons',       [LocationController::class, 'cantons']);
        Route::get('cities',        [LocationController::class, 'cities']);
        Route::get('cities/search', [LocationController::class, 'searchCities']);
    });

    // Cuisines
    Route::get('cuisines',      [CuisineController::class, 'index']);
    Route::get('cuisines/{id}', [CuisineController::class, 'show']);

    // Restaurants (specific named routes before wildcard {id})
    Route::get('restaurants/cursor',         [RestaurantController::class, 'cursor']);
    Route::get('restaurants/slug/{slug}',    [RestaurantController::class, 'showBySlug']);
    Route::get('restaurants',                [RestaurantController::class, 'index']);
    Route::get('restaurants/{id}',           [RestaurantController::class, 'show']);
    Route::get('restaurants/{id}/menu',      [RestaurantController::class, 'menu']);
    Route::get('restaurants/{id}/reviews',   [RestaurantController::class, 'reviews']);
    Route::get('restaurants/{id}/delivery-zones',  [RestaurantController::class, 'deliveryZones']);
    Route::get('restaurants/{id}/opening-hours',   [RestaurantController::class, 'openingHours']);
    Route::get('restaurants/{restaurantId}/menu-categories', [MenuController::class, 'indexCategories']);
    Route::get('restaurants/{restaurantId}/menu-items',      [MenuController::class, 'indexItems']);

    // Reviews
    Route::get('reviews/restaurant/{restaurantId}', [ReviewController::class, 'byRestaurant']);

    // Search
    Route::prefix('search')->group(function () {
        Route::get('restaurants',                      [SearchController::class, 'restaurants']);
        Route::get('restaurants/{restaurantId}/menu',  [SearchController::class, 'menuItems']);
        Route::get('suggestions',                      [SearchController::class, 'suggestions']);
    });

    // Promotions
    Route::get('promotions',       [PromotionController::class, 'index']);
    Route::get('promotions/{id}',  [PromotionController::class, 'show']);
});

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register',              [AuthController::class, 'register']);
    Route::post('register-restaurant',   [AuthController::class, 'registerRestaurant']);
    Route::post('register-courier',      [AuthController::class, 'registerCourier']);
    Route::post('login',                 [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout',           [AuthController::class, 'logout']);
        Route::get('me',                [AuthController::class, 'me']);
        Route::post('change-password',  [AuthController::class, 'changePassword']);
    });

    Route::post('forgot-password',       [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',        [AuthController::class, 'resetPassword']);
    Route::post('resend-verification',   [AuthController::class, 'resendVerification']);
    Route::post('verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail']);
});

// ── Locations (public) ───────────────────────────────────────────────────────
Route::get('cantons', [LocationController::class, 'cantons']);
Route::get('cities',  [LocationController::class, 'cities']);

// ── Cuisines ─────────────────────────────────────────────────────────────────
Route::get('cuisines',       [CuisineController::class, 'index']);
Route::get('cuisines/{id}',  [CuisineController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('cuisines',        [CuisineController::class, 'store']);
    Route::put('cuisines/{id}',    [CuisineController::class, 'update']);
    Route::delete('cuisines/{id}', [CuisineController::class, 'destroy']);
});

// ── Restaurants ───────────────────────────────────────────────────────────────
Route::get('restaurants',                                  [RestaurantController::class, 'index']);
Route::get('restaurants/{id}',                             [RestaurantController::class, 'show']);
Route::get('restaurants/{id}/menu',                        [RestaurantController::class, 'menu']);
Route::get('restaurants/{id}/reviews',                     [RestaurantController::class, 'reviews']);
Route::get('restaurants/{id}/delivery-zones',             [RestaurantController::class, 'deliveryZones']);
Route::get('restaurants/{id}/opening-hours',              [RestaurantController::class, 'openingHours']);

// ── Menu (public GETs) ────────────────────────────────────────────────────────
Route::get('restaurants/{restaurantId}/menu-categories',   [MenuController::class, 'indexCategories']);
Route::get('restaurants/{restaurantId}/menu-items',        [MenuController::class, 'indexItems']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('restaurants',           [RestaurantController::class, 'store']);
    Route::put('restaurants/{id}',       [RestaurantController::class, 'update']);
    Route::delete('restaurants/{id}',    [RestaurantController::class, 'destroy']);

    // ── Menu Categories ───────────────────────────────────────────────────────
    Route::post('restaurants/{restaurantId}/menu-categories',                 [MenuController::class, 'storeCategory']);
    Route::put('restaurants/{restaurantId}/menu-categories/{catId}',          [MenuController::class, 'updateCategory']);
    Route::delete('restaurants/{restaurantId}/menu-categories/{catId}',       [MenuController::class, 'destroyCategory']);

    // ── Menu Items ────────────────────────────────────────────────────────────
    Route::post('restaurants/{restaurantId}/menu-items',                       [MenuController::class, 'storeItem']);
    Route::put('restaurants/{restaurantId}/menu-items/{itemId}',               [MenuController::class, 'updateItem']);
    Route::delete('restaurants/{restaurantId}/menu-items/{itemId}',            [MenuController::class, 'destroyItem']);
});

// ── Users ──────────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users',         [UserController::class, 'index']);
    Route::get('users/{id}',    [UserController::class, 'show']);
    Route::put('users/{id}',    [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

// ── Orders ────────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::get('/',         [OrderController::class, 'index']);
    Route::get('/{id}',     [OrderController::class, 'show']);
    Route::post('/',        [OrderController::class, 'store']);
    Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('/{id}',  [OrderController::class, 'destroy']);
});

// ── Reviews ───────────────────────────────────────────────────────────────────
Route::get('reviews', [ReviewController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('reviews',        [ReviewController::class, 'store']);
    Route::put('reviews/{id}',    [ReviewController::class, 'update']);
    Route::delete('reviews/{id}', [ReviewController::class, 'destroy']);
});

// ── Favorites ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('favorites')->group(function () {
    Route::get('/',                      [FavoriteController::class, 'index']);
    Route::post('/',                     [FavoriteController::class, 'store']);
    Route::delete('/{restaurantId}',     [FavoriteController::class, 'destroy']);
});

// ── Search ────────────────────────────────────────────────────────────────────
Route::prefix('search')->group(function () {
    Route::get('restaurants',                          [SearchController::class, 'restaurants']);
    Route::get('restaurants/{restaurantId}/menu',      [SearchController::class, 'menuItems']);
    Route::get('suggestions',                          [SearchController::class, 'suggestions']);
});

// ── Brands ────────────────────────────────────────────────────────────────────
Route::get('brands',           [BrandController::class, 'index']);
Route::get('brands/{id}',      [BrandController::class, 'show']);
Route::get('brands/slug/{slug}', [BrandController::class, 'showBySlug']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('brands',        [BrandController::class, 'store']);
    Route::put('brands/{id}',    [BrandController::class, 'update']);
    Route::delete('brands/{id}', [BrandController::class, 'destroy']);
});

// ── Addresses ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('addresses')->group(function () {
    Route::get('/',              [AddressController::class, 'index']);
    Route::get('/{id}',          [AddressController::class, 'show']);
    Route::post('/',             [AddressController::class, 'store']);
    Route::put('/{id}',          [AddressController::class, 'update']);
    Route::delete('/{id}',       [AddressController::class, 'destroy']);
    Route::patch('/{id}/default', [AddressController::class, 'setDefault']);
});

// ── Analytics ─────────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('analytics')->group(function () {
    Route::get('dashboard',         [AnalyticsController::class, 'dashboard']);
    Route::get('revenue',           [AnalyticsController::class, 'revenue']);
    Route::get('top-restaurants',   [AnalyticsController::class, 'topRestaurants']);
});

// ── Promotions ────────────────────────────────────────────────────────────────
Route::get('promotions',              [PromotionController::class, 'index']);
Route::get('promotions/{id}',         [PromotionController::class, 'show']);
Route::post('promotions/validate',    [PromotionController::class, 'validate']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('promotions',         [PromotionController::class, 'store']);
    Route::put('promotions/{id}',     [PromotionController::class, 'update']);
    Route::delete('promotions/{id}',  [PromotionController::class, 'destroy']);
});

// ── Payments ─────────────────────────────────────────────────────────────────
// Webhook is public (Stripe calls it directly, authenticated via signature)
Route::post('payments/webhook', [PaymentController::class, 'webhook']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('payments/intent', [PaymentController::class, 'createIntent']);
});

// ── Notifications ─────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('notifications')->group(function () {
    Route::get('/',              [NotificationController::class, 'index']);
    Route::get('/count',         [NotificationController::class, 'count']);
    Route::patch('/read-all',    [NotificationController::class, 'markAllAsRead']);
    Route::patch('/{id}/read',   [NotificationController::class, 'markAsRead']);
    Route::delete('/',           [NotificationController::class, 'destroyAll']);
    Route::delete('/{id}',       [NotificationController::class, 'destroy']);
});
