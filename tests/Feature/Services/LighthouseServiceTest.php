<?php

use App\Services\LighthouseService;
use Illuminate\Support\Facades\Process;

test('scan parses a successful lighthouse run into metrics', function () {
    Process::fake([
        '*' => Process::result(output: json_encode([
            'categories' => [
                'performance' => ['score' => 0.9],
                'accessibility' => ['score' => 0.8],
                'seo' => ['score' => 1.0],
                'best-practices' => ['score' => 0.7],
            ],
            'audits' => [
                'first-contentful-paint' => ['numericValue' => 1000],
                'largest-contentful-paint' => ['numericValue' => 2000],
                'cumulative-layout-shift' => ['numericValue' => 0.05],
                'total-blocking-time' => ['numericValue' => 100],
                'speed-index' => ['numericValue' => 1500],
                'interactive' => ['numericValue' => 2500],
            ],
        ])),
    ]);

    $result = app(LighthouseService::class)->scan('https://example.test');

    expect($result['success'])->toBeTrue()
        ->and($result['metrics']['performance'])->toBe(90)
        ->and($result['metrics']['accessibility'])->toBe(80)
        ->and($result['metrics']['seo'])->toBe(100)
        ->and($result['metrics']['best_practices'])->toBe(70)
        ->and($result['metrics']['lcp'])->toBe(2000)
        ->and($result['error_message'])->toBeNull();
});

test('scan returns a failure shape when the process exits non-zero', function () {
    Process::fake([
        '*' => Process::result(output: '', errorOutput: 'binary not found', exitCode: 127),
    ]);

    $result = app(LighthouseService::class)->scan('https://example.test');

    expect($result['success'])->toBeFalse()
        ->and($result['metrics'])->toBeNull()
        ->and($result['exit_code'])->toBe(127)
        ->and($result['error_message'])->toBe('binary not found');
});

test('scan returns a failure shape when output is not valid json', function () {
    Process::fake([
        '*' => Process::result(output: 'not json'),
    ]);

    $result = app(LighthouseService::class)->scan('https://example.test');

    expect($result['success'])->toBeFalse()
        ->and($result['error_message'])->toBe('Could not parse Lighthouse JSON output.');
});
