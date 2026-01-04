<?php

namespace Database\Seeders;

use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        $data    = json_decode(file_get_contents(database_path('../data/cities.json')), true);
        $cantons = json_decode(file_get_contents(database_path('../data/cantons.json')), true);

        // Build MongoDB _id → MySQL id map for cantons
        $cantonMap = [];
        foreach ($cantons as $c) {
            $canton = Canton::where('code', $c['code'])->first();
            if ($canton) {
                $cantonMap[$c['_id']] = $canton->id;
            }
        }

        foreach ($data as $item) {
            $cantonId = $cantonMap[$item['canton_id']] ?? null;
            if (! $cantonId) {
                continue;
            }

            // Use first postal code for the city record
            $zipCode = is_array($item['postal_codes']) ? (string) $item['postal_codes'][0] : ($item['postal_code'] ?? '0000');

            City::create([
                'name'      => $item['name']['fr'] ?? $item['name']['en'],
                'canton_id' => $cantonId,
                'zip_code'  => $zipCode,
            ]);
        }
    }
}
