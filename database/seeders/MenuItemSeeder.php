<?php

namespace Database\Seeders;

use App\Domain\Menu\Models\MenuCategory;
use App\Domain\Menu\Models\MenuItem;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $items       = json_decode(file_get_contents(database_path('../data/menu_items.json')), true);
        $categories  = json_decode(file_get_contents(database_path('../data/menu_categories.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        // Build restaurant _id → local id
        $restaurantMap = [];
        foreach ($restaurants as $r) {
            $local = Restaurant::where('slug', $r['slug'])->first();
            if ($local) {
                $restaurantMap[$r['_id']] = $local->id;
            }
        }

        // Build category _id → local id
        $categoryMap = [];
        foreach ($categories as $cat) {
            $restaurantId = $restaurantMap[$cat['restaurant_id']] ?? null;
            if (! $restaurantId) {
                continue;
            }
            $local = MenuCategory::where('restaurant_id', $restaurantId)
                ->where('sort_order', $cat['sort_order'] ?? 0)
                ->first();
            if ($local) {
                $categoryMap[$cat['_id']] = $local->id;
            }
        }

        foreach ($items as $item) {
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;
            $categoryId   = $categoryMap[$item['category_id']] ?? null;

            if (! $restaurantId || $item['price'] === null) {
                continue;
            }

            MenuItem::create([
                'restaurant_id'    => $restaurantId,
                'menu_category_id' => $categoryId,
                'name'             => [
                    'fr' => $item['name']['fr'] ?? $item['name']['en'],
                    'de' => $item['name']['de'] ?? $item['name']['en'],
                    'en' => $item['name']['en'],
                ],
                'description'      => [
                    'fr' => $item['description']['fr'] ?? $item['description']['en'] ?? null,
                    'de' => $item['description']['de'] ?? $item['description']['en'] ?? null,
                    'en' => $item['description']['en'] ?? null,
                ],
                'price'            => $item['price'],
                'image'            => $item['image_url'] ?? null,
                'is_available'     => $item['is_available'] ?? true,
                'is_featured'      => $item['is_featured'] ?? false,
            ]);
        }
    }
}
