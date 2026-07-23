<?php

use App\Models\Page;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\MetricsService;

beforeEach(function () {
    $this->service = app(MetricsService::class);
});

test('dashboardSummary aggregates counts and averages', function () {
    $website = Website::factory()->create();
    $page = Page::factory()->for($website)->create();

    $completed = Scan::factory()->for($page)->create(['status' => 'completed', 'finished_at' => now()]);
    ScanResult::factory()->for($completed)->create(['performance' => 60]);

    $failed = Scan::factory()->for($page)->create(['status' => 'failed', 'finished_at' => now()->subMinute()]);
    ScanResult::factory()->for($failed)->create(['performance' => null]);

    $summary = $this->service->dashboardSummary($website->user_id);

    expect($summary['total_websites'])->toBe(1)
        ->and($summary['failed_scans'])->toBe(1)
        ->and($summary['average_performance'])->toBe(60.0)
        ->and($summary['recent_activity'])->toHaveCount(2)
        ->and($summary['last_scan']->id)->toBe($completed->id);
});

test('dashboardSummary includes recent page errors scoped to the user', function () {
    $website = Website::factory()->create();
    $page = Page::factory()->for($website)->create();
    $mine = \App\Models\PageError::factory()->for($page)->create(['last_seen_at' => now()]);

    $otherWebsite = Website::factory()->create();
    $otherPage = Page::factory()->for($otherWebsite)->create();
    \App\Models\PageError::factory()->for($otherPage)->create();

    $summary = $this->service->dashboardSummary($website->user_id);

    expect($summary['recent_page_errors'])->toHaveCount(1)
        ->and($summary['recent_page_errors']->first()->id)->toBe($mine->id);
});

test('trend returns chronological values within the requested range', function () {
    $page = Page::factory()->create();

    $old = Scan::factory()->for($page)->create(['created_at' => now()->subDays(10)]);
    ScanResult::factory()->for($old)->create(['performance' => 40]);

    $recent = Scan::factory()->for($page)->create(['created_at' => now()->subHour()]);
    ScanResult::factory()->for($recent)->create(['performance' => 70]);

    $trend = $this->service->trend($page->website->user_id, 'performance', '7d');

    expect($trend)->toHaveCount(1)
        ->and($trend->first()['value'])->toBe(70);
});

test('trend rejects an unsupported metric', function () {
    $website = Website::factory()->create();

    $this->service->trend($website->user_id, 'bogus', '7d');
})->throws(InvalidArgumentException::class);

test('trend requires from/to for a custom range', function () {
    $website = Website::factory()->create();

    $this->service->trend($website->user_id, 'performance', 'custom');
})->throws(InvalidArgumentException::class);
