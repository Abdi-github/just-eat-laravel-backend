<?php

use App\Domain\Promotion\Models\Promotion;
use Illuminate\Support\Str;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makePromotion(array $override = []): Promotion
{
    return Promotion::create(array_merge([
        'code'        => strtoupper(Str::random(8)),
        'title'       => 'Test Discount',
        'type'        => 'percentage',
        'value'       => 10,
        'is_active'   => true,
    ], $override));
}

// ─── Public endpoints ─────────────────────────────────────────────────────────

it('lists active promotions publicly', function () {
    makePromotion(['is_active' => true]);
    makePromotion(['is_active' => false]);

    $r = $this->getJson('/api/v1/promotions?active_only=true');

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonCount(1, 'data');
});

it('shows a promotion publicly', function () {
    $promo = makePromotion();

    $this->getJson("/api/v1/promotions/{$promo->id}")
         ->assertOk()
         ->assertJsonPath('success', true)
         ->assertJsonPath('data.code', $promo->code);
});

it('returns 404 for unknown promotion', function () {
    $this->getJson('/api/v1/promotions/99999')
         ->assertNotFound()
         ->assertJsonPath('success', false);
});

it('validates a promotion code', function () {
    $restaurant = createRestaurant();
    $promo = makePromotion([
        'code'          => 'SAVE10',
        'type'          => 'percentage',
        'value'         => 10,
        'minimum_order' => 20.00,
        'is_active'     => true,
    ]);

    $r = $this->postJson('/api/v1/promotions/validate', [
        'code'        => 'SAVE10',
        'order_total' => 50.00,
    ]);

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.valid', true);
});

it('rejects invalid promotion code', function () {
    $r = $this->postJson('/api/v1/promotions/validate', [
        'code'        => 'NOTEXIST',
        'order_total' => 50.00,
    ]);

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.valid', false);
});

it('rejects code with order below minimum', function () {
    makePromotion([
        'code'          => 'MINTEST',
        'minimum_order' => 100.00,
        'is_active'     => true,
    ]);

    $r = $this->postJson('/api/v1/promotions/validate', [
        'code'        => 'MINTEST',
        'order_total' => 30.00,
    ]);

    $r->assertOk()
      ->assertJsonPath('data.valid', false);
});

// ─── Auth-protected store / update / destroy ──────────────────────────────────

it('requires auth to create a promotion', function () {
    $this->postJson('/api/v1/promotions', ['code' => 'TEST', 'title' => 'Test', 'type' => 'fixed', 'value' => 5])
         ->assertUnauthorized();
});

it('admin can create a promotion', function () {
    createRole('admin');
    $admin = createUser();
    $admin->assignRole('admin');

    $r = $this->actingAs($admin)->postJson('/api/v1/promotions', [
        'code'  => 'ADMINTEST',
        'title' => 'Admin Promo',
        'type'  => 'fixed',
        'value' => 5,
    ]);

    $r->assertCreated()
      ->assertJsonPath('success', true)
      ->assertJsonPath('data.code', 'ADMINTEST');
});

it('admin can update a promotion', function () {
    createRole('admin');
    $admin = createUser();
    $admin->assignRole('admin');
    $promo = makePromotion();

    $r = $this->actingAs($admin)->putJson("/api/v1/promotions/{$promo->id}", [
        'title' => 'Updated Title',
    ]);

    $r->assertOk()
      ->assertJsonPath('data.title', 'Updated Title');
});

it('admin can delete a promotion', function () {
    createRole('admin');
    $admin = createUser();
    $admin->assignRole('admin');
    $promo = makePromotion();

    $this->actingAs($admin)->deleteJson("/api/v1/promotions/{$promo->id}")
         ->assertOk();

    expect(Promotion::find($promo->id))->toBeNull();
});

it('customer cannot create a promotion', function () {
    createRole('customer');
    $user = createUser();
    $user->assignRole('customer');

    $this->actingAs($user)->postJson('/api/v1/promotions', [
        'code'  => 'HACKTEST',
        'title' => 'Hack',
        'type'  => 'fixed',
        'value' => 99,
    ])->assertForbidden();
});
