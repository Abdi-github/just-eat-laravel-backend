<?php

it('requires authentication to list orders', function () {
    $this->getJson('/api/v1/orders')
        ->assertStatus(401);
});

it('requires authentication to place an order', function () {
    $this->postJson('/api/v1/orders', [])
        ->assertStatus(401);
});

it('returns empty order list for new customer', function () {
    $user = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 0);
});

it('places an order for authenticated user', function () {
    $user       = createUser();
    $restaurant = createRestaurant();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/orders', [
            'restaurant_id'  => $restaurant->id,
            'order_type'     => 'pickup',
            'items'          => [
                ['menu_item_id' => 1, 'name' => 'Burger', 'price' => 12.50, 'quantity' => 1],
            ],
            'subtotal'       => 12.50,
            'delivery_fee'   => 0,
            'tax'            => 1.00,
            'total'          => 13.50,
            'payment_method' => 'cash',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.restaurant.id', $restaurant->id);
});

it('customer can only see own orders', function () {
    $customerA  = createUser();
    $customerB  = createUser();
    $restaurant = createRestaurant();

    createOrder($customerA, $restaurant);
    createOrder($customerB, $restaurant);

    $response = $this->actingAs($customerA, 'sanctum')
        ->getJson('/api/v1/orders');

    $response->assertOk()
        ->assertJsonPath('pagination.total', 1);
});

it('shows an order belonging to the authenticated user', function () {
    $user       = createUser();
    $restaurant = createRestaurant();
    $order      = createOrder($user, $restaurant);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $order->id);
});

it('returns 403 when accessing another user order', function () {
    $owner      = createUser();
    $attacker   = createUser();
    $restaurant = createRestaurant();
    $order      = createOrder($owner, $restaurant);

    $response = $this->actingAs($attacker, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}");

    $response->assertStatus(403);
});

