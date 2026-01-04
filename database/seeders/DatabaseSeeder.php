<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CantonSeeder::class,
            CitySeeder::class,
            RolePermissionSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            BrandSeeder::class,
            CuisineSeeder::class,
            RestaurantSeeder::class,
            MenuCategorySeeder::class,
            MenuItemSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
            DeliveryZoneSeeder::class,
            OpeningHourSeeder::class,
            FavoriteSeeder::class,
            PromotionSeeder::class,
            StampCardSeeder::class,
        ]);
    }
}
