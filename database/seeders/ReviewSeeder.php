<?php

namespace Database\Seeders;

use App\Domain\Review\Models\Review;
use App\Domain\Order\Models\Order;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews     = json_decode(file_get_contents(database_path('../data/reviews.json')), true);
        $users       = json_decode(file_get_contents(database_path('../data/users.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);
        $orders      = json_decode(file_get_contents(database_path('../data/orders.json')), true);

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

        $orderMap = [];
        foreach ($orders as $o) {
            $local = Order::where('order_number', $o['order_number'])->first();
            if ($local) {
                $orderMap[$o['_id']] = $local->id;
            }
        }

        foreach ($reviews as $item) {
            $userId       = $userMap[$item['user_id']] ?? null;
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;

            if (! $userId || ! $restaurantId) {
                continue;
            }

            Review::create([
                'user_id'       => $userId,
                'restaurant_id' => $restaurantId,
                'order_id'      => isset($item['order_id']) ? ($orderMap[$item['order_id']] ?? null) : null,
                'rating'        => $item['rating'],
                'comment'       => $item['comment'] ?? null,
                'is_verified'   => $item['is_verified'] ?? false,
                'is_visible'    => ($item['status'] ?? 'APPROVED') === 'APPROVED',
                'created_at'    => $item['created_at'] ?? now(),
            ]);
        }
    }
}
