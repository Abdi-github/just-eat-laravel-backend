<?php

namespace Database\Seeders;

use App\Domain\Promotion\Models\Promotion;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::limit(3)->pluck('id')->toArray();
        $restaurantId = $restaurants[0] ?? null;

        $promotions = [
            [
                'restaurant_id' => null,
                'code'          => 'WELCOME10',
                'title'         => 'Welcome Discount',
                'description'   => '10% off your first order on Just Eat',
                'type'          => 'percentage',
                'value'         => 10.00,
                'minimum_order' => 20.00,
                'max_discount'  => 15.00,
                'usage_limit'   => null,
                'usage_count'   => 0,
                'is_active'     => true,
                'starts_at'     => now()->subDays(30),
                'expires_at'    => now()->addDays(60),
            ],
            [
                'restaurant_id' => null,
                'code'          => 'SUMMER20',
                'title'         => 'Summer Sale',
                'description'   => '20% off all orders this summer',
                'type'          => 'percentage',
                'value'         => 20.00,
                'minimum_order' => 30.00,
                'max_discount'  => 25.00,
                'usage_limit'   => 500,
                'usage_count'   => 42,
                'is_active'     => true,
                'starts_at'     => now()->subDays(10),
                'expires_at'    => now()->addDays(80),
            ],
            [
                'restaurant_id' => null,
                'code'          => 'FLAT5CHF',
                'title'         => 'CHF 5 Off',
                'description'   => 'CHF 5 off orders over CHF 25',
                'type'          => 'fixed',
                'value'         => 5.00,
                'minimum_order' => 25.00,
                'max_discount'  => null,
                'usage_limit'   => 1000,
                'usage_count'   => 128,
                'is_active'     => true,
                'starts_at'     => now()->subDays(60),
                'expires_at'    => now()->addDays(30),
            ],
            [
                'restaurant_id' => $restaurantId,
                'code'          => 'REST15',
                'title'         => 'Restaurant Special',
                'description'   => '15% off at selected restaurant',
                'type'          => 'percentage',
                'value'         => 15.00,
                'minimum_order' => 35.00,
                'max_discount'  => 20.00,
                'usage_limit'   => 200,
                'usage_count'   => 18,
                'is_active'     => true,
                'starts_at'     => now()->subDays(5),
                'expires_at'    => now()->addDays(25),
            ],
            [
                'restaurant_id' => null,
                'code'          => 'EXPIRED99',
                'title'         => 'Expired Promo',
                'description'   => 'This promotion has expired',
                'type'          => 'percentage',
                'value'         => 25.00,
                'minimum_order' => 0,
                'max_discount'  => null,
                'usage_limit'   => 100,
                'usage_count'   => 100,
                'is_active'     => false,
                'starts_at'     => now()->subDays(90),
                'expires_at'    => now()->subDays(1),
            ],
        ];

        foreach ($promotions as $data) {
            Promotion::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
