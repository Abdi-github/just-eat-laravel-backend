<?php

// ─── Payment API Tests ─────────────────────────────────────────────────────────

it('requires auth to create a payment intent', function () {
    $this->postJson('/api/v1/payments/intent', ['order_id' => 1])
         ->assertUnauthorized();
});

it('returns 422 when order_id is missing', function () {
    $user = createUser();

    $this->actingAs($user)->postJson('/api/v1/payments/intent', [])
         ->assertUnprocessable();
});

it('returns 422 when order does not exist', function () {
    $user = createUser();

    $this->actingAs($user)->postJson('/api/v1/payments/intent', ['order_id' => 99999])
         ->assertUnprocessable();
});

it('returns 403 when creating intent for another users order', function () {
    $user1      = createUser();
    $user2      = createUser();
    $restaurant = createRestaurant();
    $order      = createOrder($user1, $restaurant);

    $this->actingAs($user2)->postJson('/api/v1/payments/intent', ['order_id' => $order->id])
         ->assertForbidden();
});

it('returns 422 for already paid order', function () {
    $user       = createUser();
    $restaurant = createRestaurant();
    $order      = createOrder($user, $restaurant, ['payment_status' => 'paid']);

    $this->actingAs($user)->postJson('/api/v1/payments/intent', ['order_id' => $order->id])
         ->assertUnprocessable();
});

it('webhook endpoint is publicly accessible', function () {
    // Webhook requires a valid Stripe signature — without one it should return 400,
    // not 401 (meaning it's accessible but the signature check fails).
    $this->postJson('/api/v1/payments/webhook', [], ['Stripe-Signature' => 'invalid'])
         ->assertStatus(400);
});
