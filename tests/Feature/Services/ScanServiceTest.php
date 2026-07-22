<?php

use App\Models\Page;
use App\Services\LighthouseService;
use App\Services\ScanService;

test('scanPage stores a completed scan and result on success', function () {
    $page = Page::factory()->create();

    $this->mock(LighthouseService::class, function ($mock) {
        $mock->shouldReceive('scan')->once()->andReturn([
            'success' => true,
            'metrics' => ['performance' => 88, 'accessibility' => 90, 'seo' => 95, 'best_practices' => 80,
                'fcp' => 1000, 'lcp' => 2000, 'cls' => 0.01, 'tbt' => 50, 'speed_index' => 1200, 'tti' => 2200],
            'raw' => ['ok' => true],
            'exit_code' => 0,
            'error_message' => null,
        ]);
    });

    $scan = app(ScanService::class)->scanPage($page, 'manual');

    expect($scan->status)->toBe('completed')
        ->and($scan->finished_at)->not->toBeNull()
        ->and($scan->scanResult->performance)->toBe(88)
        ->and($scan->scanResult->device)->toBe('mobile');
});

test('scanPage marks the scan failed and still stores a result on lighthouse failure', function () {
    $page = Page::factory()->create();

    $this->mock(LighthouseService::class, function ($mock) {
        $mock->shouldReceive('scan')->once()->andReturn([
            'success' => false,
            'metrics' => null,
            'raw' => null,
            'exit_code' => 1,
            'error_message' => 'boom',
        ]);
    });

    $scan = app(ScanService::class)->scanPage($page, 'schedule');

    expect($scan->status)->toBe('failed')
        ->and($scan->scanResult)->not->toBeNull()
        ->and($scan->scanResult->performance)->toBeNull()
        ->and($scan->scanResult->error_message)->toBe('boom');
});
