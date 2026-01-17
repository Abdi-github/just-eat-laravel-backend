<?php

it('requires authentication to post a review', function () {
    $this->postJson('/api/v1/reviews', [])
        ->assertStatus(401);
});

it('lists reviews publicly', function () {
    $user       = createUser();
    $restaurant = createRestaurant();
    createReview($user, $restaurant);
    createReview($user, $restaurant);

    $response = $this->getJson('/api/v1/reviews');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 2);
});

it('creates a review for an authenticated user', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', [
            'restaurant_id' => $restaurant->id,
            'rating'        => 5,
            'comment'       => 'Excellent food and service!',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.rating', 5)
        ->assertJsonPath('data.restaurant_id', $restaurant->id);
});

it('validates rating is between 1 and 5', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reviews', [
            'restaurant_id' => $restaurant->id,
            'rating'        => 10,
        ]);

    $response->assertStatus(422);
});

it('owner can update their own review', function () {
    $user       = createUser();
    $restaurant = createRestaurant();
    $review     = createReview($user, $restaurant, ['rating' => 3]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/reviews/{$review->id}", [
            'rating'  => 5,
            'comment' => 'Changed my mind, it was great!',
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.rating', 5);
});

it('cannot update another users review', function () {
    $owner      = createUser();
    $attacker   = createUser();
    $restaurant = createRestaurant();
    $review     = createReview($owner, $restaurant);

    $response = $this->actingAs($attacker, 'sanctum')
        ->putJson("/api/v1/reviews/{$review->id}", [
            'rating' => 1,
        ]);

    $response->assertStatus(403);
});

it('owner can delete their own review', function () {
    $user       = createUser();
    $restaurant = createRestaurant();
    $review     = createReview($user, $restaurant);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/reviews/{$review->id}");

    $response->assertOk()
        ->assertJsonPath('success', true);
});
