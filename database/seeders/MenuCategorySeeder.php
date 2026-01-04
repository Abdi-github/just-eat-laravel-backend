<?php

namespace Database\Seeders;

use App\Domain\Menu\Models\MenuCategory;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class MenuCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories  = json_decode(file_get_contents(database_path('../data/menu_categories.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        // Build restaurant _id → local id map
        $restaurantMap = [];
        foreach ($restaurants as $r) {
            $local = Restaurant::where('slug', $r['slug'])->first();
            if ($local) {
                $restaurantMap[$r['_id']] = $local->id;
            }
        }

        foreach ($categories as $item) {
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;
            if (! $restaurantId) {
                continue;
            }

            MenuCategory::firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'sort_order'    => $item['sort_order'] ?? 0,
                ],
                [
                    'name' => [
                        'fr' => $item['name']['fr'] ?? $item['name']['en'],
                        'de' => $item['name']['de'] ?? $item['name']['en'],
                        'en' => $item['name']['en'],
                    ],
                    'is_active' => true,
                ]
            );
        }
    }
}
