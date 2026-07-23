<?php

use App\Jobs\ScanPageJob;
use App\Models\Page;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Bus;

test('scan dispatches a ScanPageJob for the owner', function () {
    Bus::fake();

    $user = User::factory()->create();
    $website = Website::factory()->for($user)->create();
    $page = Page::factory()->for($website)->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/pages/{$page->id}/scan")
        ->assertStatus(202);

    Bus::assertDispatched(ScanPageJob::class, fn ($job) => $job->page->is($page) && $job->trigger === 'manual');
});

test('scan rejects another user\'s page', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $website = Website::factory()->for($owner)->create();
    $page = Page::factory()->for($website)->create();

    $this->actingAs($intruder, 'sanctum')
        ->postJson("/api/pages/{$page->id}/scan")
        ->assertStatus(403);
});

test('scan rejects an unauthenticated request', function () {
    $page = Page::factory()->create();

    $this->postJson("/api/pages/{$page->id}/scan")->assertStatus(401);
});
