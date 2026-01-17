<?php

it('requires authentication to list favorites', function () {
    $response = $this->getJson('/api/v1/favorites');

    $response->assertUnauthorized();
});

it('returns empty favorites list for a new user', function () {
    $user = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/favorites');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(0, 'data');
});

it('adds a restaurant to favorites', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', [
            'restaurant_id' => $restaurant->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true);
});

it('returns 409 when adding a duplicate favorite', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['restaurant_id' => $restaurant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['restaurant_id' => $restaurant->id]);

    $response->assertStatus(409)
        ->assertJsonPath('success', false);
});

it('removes a restaurant from favorites', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['restaurant_id' => $restaurant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$restaurant->id}");

    $response->assertOk()
        ->assertJsonPath('success', true);
});

it('returns 404 when removing a non-existent favorite', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/favorites/{$restaurant->id}");

    $response->assertNotFound()
        ->assertJsonPath('success', false);
});

it('lists favorites after adding one', function () {
    $user       = createUser();
    $restaurant = createRestaurant(['name' => 'My Fav Place', 'slug' => 'my-fav-' . rand(1000, 9999)]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/favorites', ['restaurant_id' => $restaurant->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/favorites');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data');
});
