<?php

namespace Database\Seeders;

use App\Domain\Cuisine\Models\Cuisine;
use Illuminate\Database\Seeder;

class CuisineSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('../data/cuisines.json')), true);

        foreach ($data as $item) {
            Cuisine::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name'      => [
                        'fr' => $item['name']['fr'] ?? $item['name']['en'],
                        'de' => $item['name']['de'] ?? $item['name']['en'],
                        'en' => $item['name']['en'],
                    ],
                    'image'     => $item['image_url'] ?? null,
                    'is_active' => $item['is_active'] ?? true,
                ]
            );
        }
    }
}
