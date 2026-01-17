<?php

use App\Domain\Address\Models\Address;
use App\Domain\Cuisine\Models\Cuisine;
use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use App\Domain\Menu\Models\MenuCategory;
use App\Domain\Menu\Models\MenuItem;
use App\Domain\Order\Models\Order;
use App\Domain\Restaurant\Models\Brand;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Review\Models\Review;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(Tests\TestCase::class, RefreshDatabase::class)->in('Feature');

// ─── Helpers ──────────────────────────────────────────────────────────────────

function createUser(array $override = []): User
{
    $uid = Str::random(8);

    return User::create(array_merge([
        'username'           => 'user_' . $uid,
        'email'              => 'user_' . $uid . '@test.com',
        'password'           => 'password',
        'first_name'         => 'Test',
        'last_name'          => 'User',
        'is_active'          => true,
        'preferred_language' => 'fr',
        'email_verified_at'  => now(),
    ], $override));
}

function createRestaurant(array $override = []): Restaurant
{
    $canton = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Genève', 'de' => 'Genf']),
        'region' => 'Romandy',
    ]);

    $city = City::create([
        'name'      => 'Geneva',
        'canton_id' => $canton->id,
        'zip_code'  => '1200',
    ]);

    $address = Address::create([
        'street'    => 'Rue de la Paix',
        'zip_code'  => '1200',
        'city_id'   => $city->id,
        'canton_id' => $canton->id,
    ]);

    $owner = createUser();

    return Restaurant::create(array_merge([
        'name'                    => 'Test Restaurant',
        'slug'                    => 'test-restaurant-' . Str::random(6),
        'address_id'              => $address->id,
        'user_id'                 => $owner->id,
        'is_active'               => true,
        'is_featured'             => false,
        'price_range'             => 'moderate',
        'minimum_order'           => 10.00,
        'delivery_fee'            => 3.50,
        'estimated_delivery_time' => 30,
        'accepts_pickup'          => true,
        'accepts_delivery'        => true,
        'average_rating'          => 0,
        'total_reviews'           => 0,
    ], $override));
}

function createOrder(User $user, Restaurant $restaurant, array $override = []): Order
{
    return Order::create(array_merge([
        'user_id'        => $user->id,
        'restaurant_id'  => $restaurant->id,
        'order_number'   => 'ORD-' . strtoupper(Str::random(8)),
        'status'         => 'pending',
        'order_type'     => 'delivery',
        'items'          => [['menu_item_id' => 1, 'name' => 'Burger', 'price' => 12.50, 'quantity' => 2]],
        'subtotal'       => 25.00,
        'delivery_fee'   => 3.50,
        'tax'            => 2.00,
        'total'          => 30.50,
        'payment_method' => 'cash',
        'payment_status' => 'pending',
    ], $override));
}

function createReview(User $user, Restaurant $restaurant, array $override = []): Review
{
    return Review::create(array_merge([
        'user_id'       => $user->id,
        'restaurant_id' => $restaurant->id,
        'rating'        => 4,
        'comment'       => 'Very good food!',
        'is_verified'   => false,
        'is_visible'    => true,
    ], $override));
}

function createCuisine(array $override = []): Cuisine
{
    return Cuisine::create(array_merge([
        'name'      => json_encode(['fr' => 'Pizza', 'de' => 'Pizza', 'en' => 'Pizza']),
        'slug'      => 'cuisine-' . Str::random(6),
        'is_active' => true,
        'sort_order'=> 0,
    ], $override));
}

function createMenuCategory(Restaurant $restaurant, array $override = []): MenuCategory
{
    return MenuCategory::create(array_merge([
        'restaurant_id' => $restaurant->id,
        'name'          => json_encode(['fr' => 'Entrées', 'de' => 'Vorspeisen', 'en' => 'Starters']),
        'sort_order'    => 0,
        'is_active'     => true,
    ], $override));
}

function createMenuItem(Restaurant $restaurant, MenuCategory $category, array $override = []): MenuItem
{
    return MenuItem::create(array_merge([
        'restaurant_id'    => $restaurant->id,
        'menu_category_id' => $category->id,
        'name'             => json_encode(['fr' => 'Burger', 'de' => 'Burger', 'en' => 'Burger']),
        'price'            => 14.50,
        'is_available'     => true,
        'is_featured'      => false,
    ], $override));
}

function createBrand(array $override = []): Brand
{
    return Brand::create(array_merge([
        'name'      => 'Brand ' . \Illuminate\Support\Str::random(6),
        'slug'      => 'brand-' . \Illuminate\Support\Str::random(6),
        'is_active' => true,
    ], $override));
}

/**
 * Create (or retrieve) a Spatie role for both api and web guards.
 */
function createRole(string $name): Role
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    Role::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
    Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

    return Role::where('name', $name)->where('guard_name', 'api')->first();
}

/**
 * Create (or retrieve) a Spatie permission for the api guard.
 */
function createPermission(string $name): Permission
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'api']);
}
