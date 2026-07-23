<?php

use App\Models\Page;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\WebsiteService;

beforeEach(function () {
    $this->service = app(WebsiteService::class);
});

test('list returns websites with pages count', function () {
    $website = Website::factory()->has(Page::factory()->count(2))->create();

    $result = $this->service->list($website->user_id);

    expect($result->first()->id)->toBe($website->id)
        ->and($result->first()->pages_count)->toBe(2);
});

test('create persists a website', function () {
    $user = \App\Models\User::factory()->create();

    $website = $this->service->create($user->id, [
        'name' => 'Acme',
        'base_url' => 'https://acme.test',
        'environment' => 'production',
        'schedule' => 'daily',
    ]);

    expect($website->exists)->toBeTrue();
    $this->assertDatabaseHas('websites', ['name' => 'Acme']);
});

test('update modifies attributes', function () {
    $website = Website::factory()->create(['name' => 'Old Name']);

    $this->service->update($website, ['name' => 'New Name']);

    expect($website->fresh()->name)->toBe('New Name');
});

test('delete removes the website', function () {
    $website = Website::factory()->create();

    $this->service->delete($website);

    $this->assertDatabaseMissing('websites', ['id' => $website->id]);
});

test('setEnabled toggles the enabled flag', function () {
    $website = Website::factory()->create(['enabled' => true]);

    $this->service->setEnabled($website, false);

    expect($website->fresh()->enabled)->toBeFalse();
});

test('details computes current and previous score from performance history', function () {
    $website = Website::factory()->create(['schedule' => 'daily']);
    $page = Page::factory()->for($website)->create();

    $olderScan = Scan::factory()->for($page)->create(['created_at' => now()->subDay()]);
    ScanResult::factory()->for($olderScan)->create(['performance' => 50]);

    $newerScan = Scan::factory()->for($page)->create(['created_at' => now()]);
    ScanResult::factory()->for($newerScan)->create(['performance' => 80]);

    $details = $this->service->details($website);

    expect($details['current_score'])->toBe(80)
        ->and($details['previous_score'])->toBe(50)
        ->and($details['performance_history'])->toHaveCount(2)
        ->and($details['next_scheduled_scan'])->not->toBeNull();
});

test('details returns null next_scheduled_scan when website is disabled', function () {
    $website = Website::factory()->create(['enabled' => false, 'schedule' => 'daily']);
    Page::factory()->for($website)->create();

    $details = $this->service->details($website);

    expect($details['next_scheduled_scan'])->toBeNull();
});

test('details includes each page\'s error count', function () {
    $website = Website::factory()->create();
    $page = Page::factory()->for($website)->create();
    \App\Models\PageError::factory()->for($page)->count(2)->create();

    $details = $this->service->details($website);

    expect($details['pages']->first()->page_errors_count)->toBe(2);
});
