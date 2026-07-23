<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('sendResetLink queues a reset notification for a known email', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'jane@example.com']);

    $this->postJson('/api/auth/forgot-password', ['email' => 'jane@example.com'])
        ->assertOk();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('sendResetLink returns a 422 for an unknown email', function () {
    $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.com'])
        ->assertStatus(422);
});

test('reset changes the password with a valid token', function () {
    $user = User::factory()->create(['email' => 'jane@example.com', 'password' => 'old-password']);
    $token = Password::createToken($user);

    $this->postJson('/api/auth/reset-password', [
        'token' => $token,
        'email' => 'jane@example.com',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertOk();

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

test('reset rejects an invalid token', function () {
    $user = User::factory()->create(['email' => 'jane@example.com', 'password' => 'old-password']);

    $this->postJson('/api/auth/reset-password', [
        'token' => 'not-a-real-token',
        'email' => 'jane@example.com',
        'password' => 'new-password123',
        'password_confirmation' => 'new-password123',
    ])->assertStatus(422);

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});
