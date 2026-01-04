<?php

namespace Database\Seeders;

use App\Domain\Restaurant\Models\OpeningHour;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class OpeningHourSeeder extends Seeder
{
    public function run(): void
    {
        $hours       = json_decode(file_get_contents(database_path('../data/opening_hours.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        $restaurantMap = [];
        foreach ($restaurants as $r) {
            $local = Restaurant::where('slug', $r['slug'])->first();
            if ($local) {
                $restaurantMap[$r['_id']] = $local->id;
            }
        }

        foreach ($hours as $item) {
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;
            if (! $restaurantId) {
                continue;
            }

            OpeningHour::firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'day_of_week'   => $item['day_of_week'],
                ],
                [
                    'open_time'  => $item['open_time'] ?? null,
                    'close_time' => $item['close_time'] ?? null,
                    'is_closed'  => $item['is_closed'] ?? false,
                ]
            );
        }
    }
}
