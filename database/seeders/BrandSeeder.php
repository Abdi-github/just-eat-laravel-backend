<?php

namespace Database\Seeders;

use App\Domain\Restaurant\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('../data/brands.json')), true);

        foreach ($data as $item) {
            Brand::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name'      => $item['name'],
                    'logo'      => $item['logo_url'] ?? null,
                    'is_active' => $item['is_active'] ?? true,
                ]
            );
        }
    }
}
