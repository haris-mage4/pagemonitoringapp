<?php

use App\Models\Website;
use App\Services\UptimeService;
use Illuminate\Support\Facades\Http;

test('check records an online status for a successful response', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $website = Website::factory()->create(['base_url' => 'https://example.test']);

    $check = app(UptimeService::class)->check($website);

    expect($check->status)->toBe('online')
        ->and($check->http_code)->toBe(200)
        ->and($check->response_time_ms)->toBeGreaterThanOrEqual(0)
        ->and($check->website_id)->toBe($website->id);
});

test('check records an unavailable status for a non-2xx response', function () {
    Http::fake(['*' => Http::response('error', 500)]);

    $website = Website::factory()->create(['base_url' => 'https://example.test']);

    $check = app(UptimeService::class)->check($website);

    expect($check->status)->toBe('unavailable')
        ->and($check->http_code)->toBe(500);
});

test('check records an offline status when the connection fails', function () {
    Http::fake(function () {
        throw new Illuminate\Http\Client\ConnectionException('Connection refused');
    });

    $website = Website::factory()->create(['base_url' => 'https://example.test']);

    $check = app(UptimeService::class)->check($website);

    expect($check->status)->toBe('offline')
        ->and($check->http_code)->toBeNull();
});
