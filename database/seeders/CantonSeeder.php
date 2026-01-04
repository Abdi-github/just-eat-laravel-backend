<?php

namespace Database\Seeders;

use App\Domain\Location\Models\Canton;
use Illuminate\Database\Seeder;

class CantonSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('../data/cantons.json')), true);

        foreach ($data as $item) {
            Canton::create([
                'code'   => $item['code'],
                'name'   => [
                    'fr' => $item['name']['fr'] ?? $item['name']['en'],
                    'de' => $item['name']['de'] ?? $item['name']['en'],
                ],
                'region' => null,
            ]);
        }
    }
}
