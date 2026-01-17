<?php

it('lists all cuisines publicly', function () {
    createCuisine();
    createCuisine();

    $response = $this->getJson('/api/v1/cuisines');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

it('shows a single cuisine', function () {
    $cuisine = createCuisine();

    $response = $this->getJson("/api/v1/cuisines/{$cuisine->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $cuisine->id);
});

it('returns 404 for non-existent cuisine', function () {
    $response = $this->getJson('/api/v1/cuisines/99999');

    $response->assertNotFound()
        ->assertJsonPath('success', false);
});

it('requires authentication to create a cuisine', function () {
    $response = $this->postJson('/api/v1/cuisines', [
        'name' => ['fr' => 'Sushi'],
        'slug' => 'sushi',
    ]);

    $response->assertUnauthorized();
});

it('creates a cuisine when authenticated with permission', function () {
    $user = createUser();
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'menu.create', 'guard_name' => 'api']);
    $user->givePermissionTo('menu.create');

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/cuisines', [
            'name'       => ['fr' => 'Sushi', 'de' => 'Sushi', 'en' => 'Sushi'],
            'slug'       => 'sushi-test',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.slug', 'sushi-test');
});

it('requires authentication to delete a cuisine', function () {
    $cuisine = createCuisine();

    $response = $this->deleteJson("/api/v1/cuisines/{$cuisine->id}");

    $response->assertUnauthorized();
});

it('updates a cuisine when authenticated with permission', function () {
    $cuisine = createCuisine();
    $user    = createUser();
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'menu.update', 'guard_name' => 'api']);
    $user->givePermissionTo('menu.update');

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/cuisines/{$cuisine->id}", [
            'is_active' => false,
        ]);

    $response->assertOk()
        ->assertJsonPath('success', true);
});
