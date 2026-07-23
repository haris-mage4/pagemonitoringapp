<?php

use App\Models\Page;
use App\Models\User;
use App\Models\Website;

test('unauthenticated requests to websites are rejected', function () {
    $this->getJson('/api/websites')->assertStatus(401);
});

test('index only returns the authenticated user\'s own websites', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $mine = Website::factory()->for($user)->create();
    Website::factory()->for($otherUser)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/websites');

    $response->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $mine->id);
});

test('store attaches the website to the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/websites', [
        'name' => 'Acme',
        'base_url' => 'https://acme.test',
        'environment' => 'production',
        'schedule' => 'daily',
    ]);

    $response->assertCreated()->assertJsonPath('user_id', $user->id);
});

test('show rejects access to another user\'s website', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();

    $this->actingAs($intruder, 'sanctum')
        ->getJson("/api/websites/{$website->id}")
        ->assertStatus(403);
});

test('update rejects another user\'s website', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();

    $this->actingAs($intruder, 'sanctum')
        ->putJson("/api/websites/{$website->id}", ['name' => 'Hijacked'])
        ->assertStatus(403);
});

test('destroy rejects another user\'s website', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();

    $this->actingAs($intruder, 'sanctum')
        ->deleteJson("/api/websites/{$website->id}")
        ->assertStatus(403);

    $this->assertDatabaseHas('websites', ['id' => $website->id]);
});

test('scan rejects another user\'s website', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();

    $this->actingAs($intruder, 'sanctum')
        ->postJson("/api/websites/{$website->id}/scan")
        ->assertStatus(403);
});

test('pages of another user\'s website are rejected', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();
    $page = Page::factory()->for($website)->create();

    $this->actingAs($intruder, 'sanctum')
        ->getJson("/api/pages/{$page->id}")
        ->assertStatus(403);

    $this->actingAs($intruder, 'sanctum')
        ->getJson("/api/websites/{$website->id}/pages")
        ->assertStatus(403);
});
