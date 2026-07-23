<?php

use App\Models\User;

test('register creates a user and returns a token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()
        ->assertJsonPath('user.email', 'jane@example.com')
        ->assertJsonStructure(['user', 'token']);

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

test('register rejects a duplicate email', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('email');
});

test('register rejects mismatched password confirmation', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'not-the-same',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors('password');
});

test('login succeeds with correct credentials and returns a token', function () {
    User::factory()->create(['email' => 'jane@example.com', 'password' => 'password123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()->assertJsonStructure(['user', 'token']);
});

test('login rejects an incorrect password', function () {
    User::factory()->create(['email' => 'jane@example.com', 'password' => 'password123']);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'jane@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

test('login rejects an unknown email', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'nobody@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401);
});

test('me returns the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/auth/me');

    $response->assertOk()->assertJsonPath('email', $user->email);
});

test('me rejects an unauthenticated request', function () {
    $this->getJson('/api/auth/me')->assertStatus(401);
});

test('logout deletes the current access token', function () {
    $user = User::factory()->create();
    $user->createToken('api')->plainTextToken;
    $token = $user->createToken('api')->plainTextToken;

    expect($user->tokens()->count())->toBe(2);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/auth/logout')
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(1);
});
