<?php

it('requires authentication to list users', function () {
    $this->getJson('/api/v1/users')
        ->assertStatus(401);
});

it('can view own profile', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/users/{$user->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('returns 404 for a non-existent user', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/users/99999')
        ->assertStatus(404)
        ->assertJsonPath('success', false);
});

it('cannot view another users profile without permission', function () {
    $user  = createUser();
    $other = createUser();

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/users/{$other->id}")
        ->assertStatus(403);
});

it('can update own profile', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/users/{$user->id}", [
            'first_name' => 'Updated',
            'last_name'  => 'Name',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.first_name', 'Updated');
});

it('cannot update another users profile', function () {
    $user  = createUser();
    $other = createUser();

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/users/{$other->id}", [
            'first_name' => 'Hacked',
        ])
        ->assertStatus(403);
});

it('returns 404 when updating a non-existent user', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/users/99999', [
            'first_name' => 'Ghost',
        ])
        ->assertStatus(404)
        ->assertJsonPath('success', false);
});

it('returns 422 for duplicate username on update', function () {
    $user  = createUser(['username' => 'alice']);
    $other = createUser(['username' => 'bob']);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/users/{$user->id}", [
            'username' => 'bob',
        ])
        ->assertStatus(422);
});

it('requires authentication to delete a user', function () {
    $user = createUser();

    $this->deleteJson("/api/v1/users/{$user->id}")
        ->assertStatus(401);
});

it('cannot delete a user without permission', function () {
    $user   = createUser();
    $target = createUser();

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/users/{$target->id}")
        ->assertStatus(403);
});
