<?php

namespace Database\Seeders;

use App\Domain\Address\Models\Address;
use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use App\Domain\Restaurant\Models\Brand;
use App\Domain\Restaurant\Models\Restaurant;
use App\Domain\Cuisine\Models\Cuisine;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants      = json_decode(file_get_contents(database_path('../data/restaurants.json')), true);
        $cities           = json_decode(file_get_contents(database_path('../data/cities.json')), true);
        $cantons          = json_decode(file_get_contents(database_path('../data/cantons.json')), true);
        $brands           = json_decode(file_get_contents(database_path('../data/brands.json')), true);
        $restaurantCuisines = json_decode(file_get_contents(database_path('../data/restaurant_cuisines.json')), true);

        // Build _id → local id maps
        $cityMap = [];
        foreach ($cities as $city) {
            $local = City::where('zip_code', is_array($city['postal_codes']) ? (string)$city['postal_codes'][0] : '0000')->first();
            if ($local) {
                $cityMap[$city['_id']] = $local->id;
            }
        }

        $cantonMap = [];
        foreach ($cantons as $c) {
            $local = Canton::where('code', $c['code'])->first();
            if ($local) {
                $cantonMap[$c['_id']] = $local->id;
            }
        }

        $brandMap = [];
        foreach ($brands as $b) {
            $local = Brand::where('slug', $b['slug'])->first();
            if ($local) {
                $brandMap[$b['_id']] = $local->id;
            }
        }

        // Get a default owner (first restaurant_owner user)
        $defaultOwner = User::role('restaurant_owner')->first() ?? User::first();

        // Build restaurant _id → local id map for cuisine pivot
        $restaurantIdMap = [];

        foreach ($restaurants as $item) {
            $cityId   = $cityMap[$item['city_id']] ?? null;
            $cantonId = $cantonMap[$item['canton_id']] ?? null;
            $brandId  = isset($item['brand_id']) ? ($brandMap[$item['brand_id']] ?? null) : null;

            // Address FK is required — create one per restaurant
            $addressCityId   = $cityId   ?? City::first()?->id   ?? 1;
            $addressCantonId = $cantonId ?? Canton::first()?->id ?? 1;

            $address = Address::create([
                'street'    => $item['address'] ?? 'Unknown Street 1',
                'zip_code'  => $item['postal_code'] ?? '0000',
                'city_id'   => $addressCityId,
                'canton_id' => $addressCantonId,
            ]);

            $restaurant = Restaurant::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name'           => $item['name'],
                    'description'    => $item['description'] ?? null,
                    'brand_id'       => $brandId,
                    'address_id'     => $address->id,
                    'user_id'        => $defaultOwner?->id ?? 1,
                    'phone'          => $item['phone'] ?? null,
                    'email'          => $item['email'] ?? null,
                    'logo'           => $item['logo_url'] ?? null,
                    'cover_image'    => $item['cover_image_url'] ?? null,
                    'is_active'      => $item['is_active'] ?? true,
                    'is_featured'    => $item['is_featured'] ?? false,
                    'average_rating' => $item['rating'] ?? 0,
                    'total_reviews'  => $item['review_count'] ?? 0,
                    'delivery_fee'   => $item['delivery_fee'] ?? 0,
                    'minimum_order'  => $item['minimum_order'] ?? 0,
                ]
            );

            $restaurantIdMap[$item['_id']] = $restaurant->id;
        }

        // Build cuisine _id → local id map
        $cuisines     = json_decode(file_get_contents(database_path('../data/cuisines.json')), true);
        $cuisineIdMap = [];
        foreach ($cuisines as $c) {
            $local = Cuisine::where('slug', $c['slug'])->first();
            if ($local) {
                $cuisineIdMap[$c['_id']] = $local->id;
            }
        }

        // Attach cuisines to restaurants
        foreach ($restaurantCuisines as $rc) {
            $restaurantId = $restaurantIdMap[$rc['restaurant_id']] ?? null;
            $cuisineId    = $cuisineIdMap[$rc['cuisine_id']] ?? null;

            if ($restaurantId && $cuisineId) {
                $restaurant = Restaurant::find($restaurantId);
                if ($restaurant && ! $restaurant->cuisines()->where('cuisine_id', $cuisineId)->exists()) {
                    $restaurant->cuisines()->attach($cuisineId);
                }
            }
        }
    }
}
