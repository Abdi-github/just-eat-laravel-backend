<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::create(['name' => 'customer', 'guard_name' => 'api']);
    Role::create(['name' => 'customer', 'guard_name' => 'web']);
});

it('registers a new user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'username'              => 'johndoe',
        'email'                 => 'john@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['user' => ['id', 'email', 'username'], 'token']]);
});

it('returns 422 for duplicate email on register', function () {
    createUser(['email' => 'dup@example.com', 'username' => 'existing_user']);

    $response = $this->postJson('/api/v1/auth/register', [
        'username'              => 'newuser',
        'email'                 => 'dup@example.com',
        'password'              => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('logs in with valid credentials', function () {
    createUser(['email' => 'login@example.com', 'password' => 'mysecret']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'login@example.com',
        'password' => 'mysecret',
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['user' => ['id', 'email'], 'token']]);
});

it('returns 422 for wrong password', function () {
    createUser(['email' => 'auth@example.com']);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'auth@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
});

it('returns current user on GET me', function () {
    $user = createUser();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('logs out and invalidates token', function () {
    $user  = createUser();
    $token = $user->createToken('api')->plainTextToken;

    $response = $this->withToken($token)
        ->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJsonPath('success', true);
});

it('returns 401 for unauthenticated GET me', function () {
    $this->getJson('/api/v1/auth/me')
        ->assertStatus(401);
});

