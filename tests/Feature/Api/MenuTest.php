<?php

it('lists menu categories for a restaurant publicly', function () {
    $restaurant = createRestaurant();
    createMenuCategory($restaurant);
    createMenuCategory($restaurant);

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/menu-categories");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

it('lists menu items for a restaurant publicly', function () {
    $restaurant = createRestaurant();
    $category   = createMenuCategory($restaurant);
    createMenuItem($restaurant, $category);
    createMenuItem($restaurant, $category);

    $response = $this->getJson("/api/v1/restaurants/{$restaurant->id}/menu-items");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data');
});

it('requires authentication to create a menu category', function () {
    $restaurant = createRestaurant();

    $response = $this->postJson("/api/v1/restaurants/{$restaurant->id}/menu-categories", [
        'name' => ['fr' => 'Desserts'],
    ]);

    $response->assertUnauthorized();
});

it('creates a menu category when authenticated', function () {
    $restaurant = createRestaurant();
    $user       = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/restaurants/{$restaurant->id}/menu-categories", [
            'name'       => ['fr' => 'Desserts', 'de' => 'Desserts', 'en' => 'Desserts'],
            'sort_order' => 1,
            'is_active'  => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Desserts');
});

it('creates a menu item when authenticated', function () {
    $restaurant = createRestaurant();
    $category   = createMenuCategory($restaurant);
    $user       = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/restaurants/{$restaurant->id}/menu-items", [
            'menu_category_id' => $category->id,
            'name'             => ['fr' => 'Tarte Tatin', 'de' => 'Apfelkuchen', 'en' => 'Apple Tart'],
            'price'            => 8.50,
            'is_available'     => true,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.price', '8.50');
});

it('requires authentication to delete a menu category', function () {
    $restaurant = createRestaurant();
    $category   = createMenuCategory($restaurant);

    $response = $this->deleteJson("/api/v1/restaurants/{$restaurant->id}/menu-categories/{$category->id}");

    $response->assertUnauthorized();
});

it('deletes a menu item when authenticated', function () {
    $restaurant = createRestaurant();
    $category   = createMenuCategory($restaurant);
    $item       = createMenuItem($restaurant, $category);
    $user       = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/restaurants/{$restaurant->id}/menu-items/{$item->id}");

    $response->assertOk()
        ->assertJsonPath('success', true);
});
