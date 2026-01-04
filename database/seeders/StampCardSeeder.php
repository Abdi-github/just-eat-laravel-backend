<?php

namespace Database\Seeders;

use App\Domain\Promotion\Models\StampCard;
use App\Domain\Restaurant\Models\Restaurant;
use Illuminate\Database\Seeder;

class StampCardSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = Restaurant::limit(5)->get();

        if ($restaurants->isEmpty()) {
            return;
        }

        $cards = [
            [
                'name'               => 'Loyalty Burger Card',
                'description'        => 'Collect 10 stamps and get a free burger!',
                'stamps_required'    => 10,
                'reward_description' => 'Free Classic Burger',
                'reward_type'        => 'FLAT',
                'reward_value'       => 14.50,
                'valid_from'         => now()->startOfMonth(),
                'valid_until'        => now()->addYear(),
                'is_active'          => true,
            ],
            [
                'name'               => 'Pizza Lover Card',
                'description'        => 'Earn 8 stamps for 20% off your next pizza order',
                'stamps_required'    => 8,
                'reward_description' => '20% discount on next order',
                'reward_type'        => 'PERCENTAGE',
                'reward_value'       => 20.00,
                'valid_from'         => now()->subDays(15),
                'valid_until'        => now()->addMonths(6),
                'is_active'          => true,
            ],
            [
                'name'               => 'Sushi Collector Card',
                'description'        => 'Collect 12 stamps for CHF 15 off',
                'stamps_required'    => 12,
                'reward_description' => 'CHF 15 off your next order',
                'reward_type'        => 'FLAT',
                'reward_value'       => 15.00,
                'valid_from'         => now(),
                'valid_until'        => now()->addYear(),
                'is_active'          => true,
            ],
            [
                'name'               => 'Seasonal Special Card',
                'description'        => 'Limited time: 5 stamps for a free desert',
                'stamps_required'    => 5,
                'reward_description' => 'Free dessert of your choice',
                'reward_type'        => 'FLAT',
                'reward_value'       => 8.00,
                'valid_from'         => now()->subDays(30),
                'valid_until'        => now()->subDays(1), // expired
                'is_active'          => false,
            ],
        ];

        foreach ($cards as $index => $data) {
            $restaurant = $restaurants->get($index % $restaurants->count());
            StampCard::firstOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'name'          => $data['name'],
                ],
                array_merge($data, ['restaurant_id' => $restaurant->id])
            );
        }
    }
}
