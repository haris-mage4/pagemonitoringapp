<?php

use App\Models\Page;
use App\Models\Scan;
use App\Models\ScanResult;
use App\Models\Website;
use App\Services\PageService;

beforeEach(function () {
    $this->service = app(PageService::class);
});

test('listForWebsite returns only that website\'s pages', function () {
    $website = Website::factory()->has(Page::factory()->count(2))->create();
    Page::factory()->for(Website::factory())->create();

    $pages = $this->service->listForWebsite($website);

    expect($pages)->toHaveCount(2);
});

test('create attaches the page to the website', function () {
    $website = Website::factory()->create();

    $page = $this->service->create($website, [
        'url' => 'https://acme.test/home',
        'page_type' => 'homepage',
    ]);

    expect($page->website_id)->toBe($website->id);
});

test('setEnabled toggles the page', function () {
    $page = Page::factory()->create(['enabled' => true]);

    $this->service->setEnabled($page, false);

    expect($page->fresh()->enabled)->toBeFalse();
});

test('details includes scan history, performance history, and raw report', function () {
    $page = Page::factory()->create();

    $scan = Scan::factory()->for($page)->create(['finished_at' => now()]);
    ScanResult::factory()->for($scan)->create(['performance' => 90, 'raw_json' => ['ok' => true]]);

    $failedScan = Scan::factory()->for($page)->create(['status' => 'failed', 'finished_at' => now()->subHour()]);
    ScanResult::factory()->for($failedScan)->create(['performance' => null]);

    $details = $this->service->details($page);

    expect($details['scan_history'])->toHaveCount(2)
        ->and($details['performance_history'])->toHaveCount(1)
        ->and($details['latest_scan']->id)->toBe($scan->id)
        ->and($details['raw_report'])->toBe(['ok' => true]);
});
