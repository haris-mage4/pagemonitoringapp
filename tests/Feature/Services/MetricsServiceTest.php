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

test('trend returns only the last 5 scans in chronological order', function () {
    $page = Page::factory()->create();

    foreach ([10, 20, 30, 40, 50, 60, 70] as $i => $performance) {
        $scan = Scan::factory()->for($page)->create(['created_at' => now()->subMinutes(70 - $i * 10)]);
        ScanResult::factory()->for($scan)->create(['performance' => $performance]);
    }

    $trend = $this->service->trend($page->website->user_id, 'performance');

    expect($trend)->toHaveCount(5)
        ->and($trend->pluck('value')->all())->toBe([30, 40, 50, 60, 70]);
});

test('trend rejects an unsupported metric', function () {
    $website = Website::factory()->create();

    $this->service->trend($website->user_id, 'bogus');
})->throws(InvalidArgumentException::class);

test('dashboardSummary includes each page with its latest scan', function () {
    $website = Website::factory()->create();
    $page = Page::factory()->for($website)->create();
    $scan = Scan::factory()->for($page)->create(['status' => 'completed', 'finished_at' => now()]);
    ScanResult::factory()->for($scan)->create(['performance' => 88]);

    $summary = $this->service->dashboardSummary($website->user_id);

    expect($summary['pages'])->toHaveCount(1)
        ->and($summary['pages']->first()->latestScan->id)->toBe($scan->id);
});
