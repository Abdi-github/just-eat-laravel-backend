<?php

namespace Database\Seeders;

use App\Domain\Restaurant\Models\Favorite;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favorites   = json_decode(file_get_contents(database_path('../data/favorites.json')), true);
        $users       = json_decode(file_get_contents(database_path('../data/users.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        $userMap = [];
        foreach ($users as $u) {
            $local = User::where('email', $u['email'])->first();
            if ($local) {
                $userMap[$u['_id']] = $local->id;
            }
        }

        $restaurantMap = [];
        foreach ($restaurants as $r) {
            $local = Restaurant::where('slug', $r['slug'])->first();
            if ($local) {
                $restaurantMap[$r['_id']] = $local->id;
            }
        }

        foreach ($favorites as $item) {
            $userId       = $userMap[$item['user_id']] ?? null;
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;

            if (! $userId || ! $restaurantId) {
                continue;
            }

            Favorite::firstOrCreate([
                'user_id'       => $userId,
                'restaurant_id' => $restaurantId,
            ]);
        }
    }
}
