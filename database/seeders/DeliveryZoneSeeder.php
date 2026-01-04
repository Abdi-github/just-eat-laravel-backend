<?php

namespace Database\Seeders;

use App\Domain\Delivery\Models\DeliveryZone;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones       = json_decode(file_get_contents(database_path('../data/delivery_zones.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        $restaurantMap = [];
        foreach ($restaurants as $r) {
            $local = Restaurant::where('slug', $r['slug'])->first();
            if ($local) {
                $restaurantMap[$r['_id']] = $local->id;
            }
        }

        foreach ($zones as $item) {
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;
            if (! $restaurantId) {
                continue;
            }

            DeliveryZone::create([
                'restaurant_id'    => $restaurantId,
                'zone_name'        => implode(', ', $item['postal_codes'] ?? []),
                'radius_km'        => 5.00,
                'delivery_fee'     => $item['delivery_fee'] ?? 0,
                'minimum_order'    => $item['minimum_order'] ?? 0,
                'estimated_time'   => $item['estimated_delivery_minutes']['max'] ?? 45,
            ]);
        }
    }
}
