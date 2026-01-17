<?php

it('lists restaurants with pagination', function () {
    createRestaurant(['name' => 'Alpha Kitchen', 'slug' => 'alpha-kitchen']);
    createRestaurant(['name' => 'Beta Grill', 'slug' => 'beta-grill']);

    $response = $this->getJson('/api/v1/restaurants');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data', 'pagination' => ['page', 'limit', 'total', 'totalPages']])
        ->assertJsonPath('pagination.total', 2);
});

it('filters restaurants by is_featured', function () {
    createRestaurant(['name' => 'Featured Place', 'slug' => 'featured-place', 'is_featured' => true]);
    createRestaurant(['name' => 'Regular Place', 'slug' => 'regular-place', 'is_featured' => false]);

    $response = $this->getJson('/api/v1/restaurants?is_featured=1');

    $response->assertOk()
        ->assertJsonPath('pagination.total', 1)
        ->assertJsonPath('data.0.name', 'Featured Place');
});

it('shows a single restaurant', function () {
    $restaurant = createRestaurant(['name' => 'Solo Diner', 'slug' => 'solo-diner']);

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $restaurant->id)
        ->assertJsonPath('data.name', 'Solo Diner');
});

it('returns 404 for non-existent restaurant', function () {
    $response = $this->getJson('/api/v1/restaurants/99999');

    $response->assertStatus(404)
        ->assertJsonPath('success', false);
});

it('returns restaurant menu categories', function () {
    $restaurant = createRestaurant();

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/menu");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data']);
});

it('returns restaurant delivery zones', function () {
    $restaurant = createRestaurant();

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/delivery-zones");

    $response->assertOk()
        ->assertJsonPath('success', true);
});

it('returns restaurant opening hours', function () {
    $restaurant = createRestaurant();

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/opening-hours");

    $response->assertOk()
        ->assertJsonPath('success', true);
});

