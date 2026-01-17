<?php

it('lists brands publicly', function () {
    createBrand();
    createBrand();

    $response = $this->getJson('/api/v1/brands');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('pagination.total', 2);
});

it('shows a single brand', function () {
    $brand = createBrand();

    $response = $this->getJson("/api/v1/brands/{$brand->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $brand->id);
});

it('returns 404 for non-existent brand', function () {
    $response = $this->getJson('/api/v1/brands/99999');

    $response->assertNotFound()
        ->assertJsonPath('success', false);
});

it('shows a brand by slug', function () {
    $brand = createBrand(['slug' => 'my-brand-slug']);

    $response = $this->getJson('/api/v1/brands/slug/my-brand-slug');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.slug', 'my-brand-slug');
});

it('requires authentication to create a brand', function () {
    $response = $this->postJson('/api/v1/brands', [
        'name' => 'New Brand',
        'slug' => 'new-brand',
    ]);

    $response->assertUnauthorized();
});

it('creates a brand when authenticated', function () {
    $user = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/brands', [
            'name'      => 'McBrand',
            'slug'      => 'mcbrand-test',
            'is_active' => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.slug', 'mcbrand-test');
});

it('updates a brand when authenticated', function () {
    $brand = createBrand();
    $user  = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/brands/{$brand->id}", [
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);
});

it('deletes a brand when authenticated', function () {
    $brand = createBrand();
    $user  = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/brands/{$brand->id}");

    $response->assertOk()
        ->assertJsonPath('success', true);
});
