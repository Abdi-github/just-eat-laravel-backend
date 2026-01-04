<?php

namespace Database\Seeders;

use App\Domain\Order\Models\Order;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orders      = json_decode(file_get_contents(database_path('../data/orders.json')), true);
        $users       = json_decode(file_get_contents(database_path('../data/users.json')), true);
        $restaurants = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);

        // Build _id maps
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

        // Status mapping from Node.js to Laravel enum
        $statusMap = [
            'PENDING'    => 'pending',
            'CONFIRMED'  => 'confirmed',
            'PREPARING'  => 'preparing',
            'READY'      => 'picked_up',
            'PICKED_UP'  => 'picked_up',
            'DELIVERED'  => 'delivered',
            'CANCELLED'  => 'cancelled',
        ];

        $paymentStatusMap = [
            'PAID'     => 'paid',
            'PENDING'  => 'pending',
            'FAILED'   => 'failed',
            'REFUNDED' => 'refunded',
        ];

        foreach ($orders as $item) {
            $userId       = $userMap[$item['user_id']] ?? null;
            $restaurantId = $restaurantMap[$item['restaurant_id']] ?? null;

            if (! $userId || ! $restaurantId) {
                continue;
            }

            Order::firstOrCreate(
                ['order_number' => $item['order_number']],
                [
                    'user_id'          => $userId,
                    'restaurant_id'    => $restaurantId,
                    'status'           => $statusMap[$item['status']] ?? 'pending',
                    'order_type'       => $item['order_type'] ?? 'delivery',
                    'items'            => $item['items'],
                    'subtotal'         => $item['subtotal'],
                    'delivery_fee'     => $item['delivery_fee'] ?? 0,
                    'tax'              => $item['service_fee'] ?? 0,
                    'total'            => $item['total'],
                    'payment_method'   => 'credit_card',
                    'payment_status'   => $paymentStatusMap[$item['payment_status']] ?? 'pending',
                    'special_instructions' => $item['special_instructions'] ?? null,
                    'created_at'       => $item['created_at'] ?? now(),
                ]
            );
        }
    }
}
