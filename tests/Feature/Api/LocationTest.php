<?php

use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use Illuminate\Support\Str;

it('lists all cantons publicly', function () {
    $canton = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Vaud', 'de' => 'Waadt']),
        'region' => 'West',
    ]);

    $response = $this->getJson('/api/v1/cantons');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data']);
});

it('lists all cities publicly', function () {
    $canton = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Genève', 'de' => 'Genf']),
        'region' => 'Romandy',
    ]);

    City::create(['name' => 'Geneva', 'canton_id' => $canton->id, 'zip_code' => '1200']);
    City::create(['name' => 'Carouge', 'canton_id' => $canton->id, 'zip_code' => '1227']);

    $response = $this->getJson('/api/v1/cities');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

it('filters cities by canton_id', function () {
    $cantonA = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Genève', 'de' => 'Genf']),
        'region' => 'Romandy',
    ]);
    $cantonB = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Vaud', 'de' => 'Waadt']),
        'region' => 'Romandy',
    ]);

    City::create(['name' => 'Geneva',   'canton_id' => $cantonA->id, 'zip_code' => '1200']);
    City::create(['name' => 'Lausanne', 'canton_id' => $cantonB->id, 'zip_code' => '1000']);

    $response = $this->getJson("/api/v1/cities?canton_id={$cantonA->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Geneva');
});
