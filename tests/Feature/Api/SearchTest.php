<?php

it('searches restaurants publicly', function () {
    createRestaurant(['name' => 'Pizza Palace', 'is_active' => true]);
    createRestaurant(['name' => 'Sushi World', 'is_active' => true]);

    $response = $this->getJson('/api/v1/search/restaurants?q=Pizza');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data');
});

it('returns all active restaurants when no query', function () {
    createRestaurant(['is_active' => true]);
    createRestaurant(['is_active' => true]);

    $response = $this->getJson('/api/v1/search/restaurants');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 2);
});

it('searches menu items within a restaurant', function () {
    $restaurant = createRestaurant(['is_active' => true]);
    $category   = createMenuCategory($restaurant);
    createMenuItem($restaurant, $category, [
        'name'         => json_encode(['fr' => 'Burger Classique', 'de' => 'Burger', 'en' => 'Classic Burger']),
        'is_available' => true,
    ]);
    createMenuItem($restaurant, $category, [
        'name'         => json_encode(['fr' => 'Pizza', 'de' => 'Pizza', 'en' => 'Pizza']),
        'is_available' => true,
    ]);

    $response = $this->getJson("/api/v1/search/restaurants/{$restaurant->id}/menu?q=Burger");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 1);
});

it('returns 404 for menu search on non-existent restaurant', function () {
    $response = $this->getJson('/api/v1/search/restaurants/99999/menu?q=burger');

    $response->assertNotFound();
});

it('returns suggestions for a query', function () {
    createRestaurant(['name' => 'Burger Barn', 'is_active' => true]);
    createRestaurant(['name' => 'Sushi Place', 'is_active' => true]);

    $response = $this->getJson('/api/v1/search/suggestions?q=Burger');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.restaurants');
});

it('returns empty suggestions for blank query', function () {
    $response = $this->getJson('/api/v1/search/suggestions?q=');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(0, 'data.restaurants')
        ->assertJsonCount(0, 'data.cuisines');
});
