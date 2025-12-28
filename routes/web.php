<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AnalyticsAdminController;
use App\Http\Controllers\Admin\ApplicationController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CuisineController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentAdminController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\RestaurantController;
use App\Http\Controllers\Admin\RbacController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StampCardController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/admin/login'));

// ── Admin Guest Routes ────────────────────────────────────────────────────────
Route::middleware('guest:web')->group(function () {
    Route::get('/admin/login',  [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/admin/login', [LoginController::class, 'login']);
});

// ── Admin Protected Routes ────────────────────────────────────────────────────
Route::middleware('auth:web')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/',          fn () => redirect('/admin/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('restaurants/pending',            [RestaurantController::class, 'pending'])->name('restaurants.pending');
    Route::resource('restaurants', RestaurantController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
    Route::patch('restaurants/{id}/approve',     [RestaurantController::class, 'approve'])->name('restaurants.approve');
    Route::delete('restaurants/{id}/reject',     [RestaurantController::class, 'reject'])->name('restaurants.reject');
    Route::resource('users',       UserController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::resource('orders',      OrderController::class)->only(['index', 'show', 'update']);
    Route::resource('cuisines',    CuisineController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
    Route::resource('reviews',     ReviewController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::resource('brands',      BrandController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);

    // ── Location (cantons + cities) ───────────────────────────────────────────
    Route::get('locations',                    [LocationController::class, 'index'])->name('locations.index');
    Route::post('locations/cantons',           [LocationController::class, 'storeCanton'])->name('locations.cantons.store');
    Route::put('locations/cantons/{id}',       [LocationController::class, 'updateCanton'])->name('locations.cantons.update');
    Route::delete('locations/cantons/{id}',    [LocationController::class, 'destroyCanton'])->name('locations.cantons.destroy');
    Route::post('locations/cities',            [LocationController::class, 'storeCity'])->name('locations.cities.store');
    Route::put('locations/cities/{id}',        [LocationController::class, 'updateCity'])->name('locations.cities.update');
    Route::delete('locations/cities/{id}',     [LocationController::class, 'destroyCity'])->name('locations.cities.destroy');

    // ── Promotions ─────────────────────────────────────────────────────────────
    Route::resource('promotions', PromotionController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);
    Route::resource('stamp-cards', StampCardController::class)->only(['index', 'show', 'create', 'store', 'update', 'destroy']);

    // ── Analytics ──────────────────────────────────────────────────────────────
    Route::get('analytics',                                [AnalyticsAdminController::class, 'index'])->name('analytics.index');

    // ── Notifications (admin broadcast) ───────────────────────────────────────
    Route::get('notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/send',     [NotificationController::class, 'send'])->name('notifications.send');
    Route::post('notifications/send-all', [NotificationController::class, 'sendToAll'])->name('notifications.send-all');

    // ── Menu Management (per restaurant) ──────────────────────────────────────
    Route::get('restaurants/{restaurantId}/menu',                         [MenuController::class, 'index'])->name('menu.index');
    Route::post('restaurants/{restaurantId}/menu/categories',             [MenuController::class, 'storeCategory'])->name('menu.categories.store');
    Route::put('restaurants/{restaurantId}/menu/categories/{catId}',      [MenuController::class, 'updateCategory'])->name('menu.categories.update');
    Route::delete('restaurants/{restaurantId}/menu/categories/{catId}',   [MenuController::class, 'destroyCategory'])->name('menu.categories.destroy');
    Route::post('restaurants/{restaurantId}/menu/items',                  [MenuController::class, 'storeItem'])->name('menu.items.store');
    Route::put('restaurants/{restaurantId}/menu/items/{itemId}',          [MenuController::class, 'updateItem'])->name('menu.items.update');
    Route::delete('restaurants/{restaurantId}/menu/items/{itemId}',       [MenuController::class, 'destroyItem'])->name('menu.items.destroy');

    // ── Applications ───────────────────────────────────────────────────────────
    Route::get('applications',                          [ApplicationController::class, 'index'])->name('applications.index');
    Route::patch('applications/{userId}/approve',       [ApplicationController::class, 'approve'])->name('applications.approve');
    Route::patch('applications/{userId}/reject',        [ApplicationController::class, 'reject'])->name('applications.reject');

    // ── Deliveries ─────────────────────────────────────────────────────────────
    Route::get('deliveries',                            [DeliveryController::class, 'index'])->name('deliveries.index');
    Route::get('deliveries/{id}',                       [DeliveryController::class, 'show'])->name('deliveries.show');
    Route::post('deliveries',                           [DeliveryController::class, 'store'])->name('deliveries.store');
    Route::patch('deliveries/{id}/assign',              [DeliveryController::class, 'assignCourier'])->name('deliveries.assign');
    Route::patch('deliveries/{id}/status',              [DeliveryController::class, 'updateStatus'])->name('deliveries.status');

    // ── Payment Transactions ───────────────────────────────────────────────────
    Route::get('payments',                              [PaymentAdminController::class, 'index'])->name('payments.index');
    Route::get('payments/{id}',                         [PaymentAdminController::class, 'show'])->name('payments.show');
    Route::post('payments/{orderId}/refund',            [PaymentAdminController::class, 'refund'])->name('payments.refund');

    // ── RBAC (Roles & Permissions) ─────────────────────────────────────────────
    Route::get('rbac',                                      [RbacController::class, 'index'])->name('rbac.index');
    Route::post('rbac/roles',                               [RbacController::class, 'storeRole'])->name('rbac.roles.store');
    Route::put('rbac/roles/{id}',                           [RbacController::class, 'updateRole'])->name('rbac.roles.update');
    Route::delete('rbac/roles/{id}',                        [RbacController::class, 'destroyRole'])->name('rbac.roles.destroy');
    Route::put('rbac/roles/{id}/permissions',               [RbacController::class, 'syncPermissions'])->name('rbac.roles.permissions.sync');
    Route::post('rbac/permissions',                          [RbacController::class, 'storePermission'])->name('rbac.permissions.store');
    Route::delete('rbac/permissions/{id}',                  [RbacController::class, 'destroyPermission'])->name('rbac.permissions.destroy');

    // ── Settings ───────────────────────────────────────────────────────────────
    Route::get('settings',                              [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings/language',                   [SettingsController::class, 'updateLanguage'])->name('settings.language');
});

// ── Admin Logout ──────────────────────────────────────────────────────────────
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('logout');
