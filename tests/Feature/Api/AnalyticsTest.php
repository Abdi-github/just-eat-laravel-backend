<?php

// ─── Analytics API Tests ───────────────────────────────────────────────────────

beforeEach(function () {
    createRole('admin');
    createRole('restaurant_owner');
    createRole('customer');
});

it('requires auth for analytics dashboard', function () {
    $this->getJson('/api/v1/analytics/dashboard')->assertUnauthorized();
});

it('forbids customer from accessing dashboard', function () {
    $user = createUser();
    $user->assignRole('customer');

    $this->actingAs($user)->getJson('/api/v1/analytics/dashboard')
         ->assertForbidden();
});

it('admin can access analytics dashboard', function () {
    $admin = createUser();
    $admin->assignRole('admin');

    $r = $this->actingAs($admin)->getJson('/api/v1/analytics/dashboard');

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonStructure(['data' => [
          'total_users',
          'total_restaurants',
          'total_orders',
          'total_revenue',
          'pending_orders',
          'active_restaurants',
      ]]);
});

it('restaurant owner can access dashboard with scoped stats', function () {
    $owner = createUser();
    $owner->assignRole('restaurant_owner');

    $r = $this->actingAs($owner)->getJson('/api/v1/analytics/dashboard');

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.total_users', null); // owners don't get user count
});

it('admin can access revenue analytics', function () {
    $admin = createUser();
    $admin->assignRole('admin');

    $r = $this->actingAs($admin)->getJson('/api/v1/analytics/revenue?period=monthly');

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.period', 'monthly');
});

it('admin can access revenue for different periods', function () {
    $admin = createUser();
    $admin->assignRole('admin');

    foreach (['daily', 'weekly', 'monthly'] as $period) {
        $this->actingAs($admin)->getJson("/api/v1/analytics/revenue?period={$period}")
             ->assertOk()
             ->assertJsonPath('data.period', $period);
    }
});

it('only admin can access top restaurants', function () {
    $owner = createUser();
    $owner->assignRole('restaurant_owner');

    $this->actingAs($owner)->getJson('/api/v1/analytics/top-restaurants')
         ->assertForbidden();
});

it('admin can get top restaurants', function () {
    $admin = createUser();
    $admin->assignRole('admin');

    $r = $this->actingAs($admin)->getJson('/api/v1/analytics/top-restaurants');

    $r->assertOk()->assertJsonPath('success', true);
});
