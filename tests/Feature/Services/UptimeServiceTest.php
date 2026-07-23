<?php

use App\Models\UptimeCheck;
use App\Models\Website;
use App\Notifications\WebsiteUptimeStatusChanged;
use App\Services\UptimeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

test('check records an online status for a successful response', function () {
    Http::fake(['*' => Http::response('ok', 200)]);

    $website = Website::factory()->create(['base_url' => 'https://example.test']);

    $check = app(UptimeService::class)->check($website);

    expect($check->status)->toBe('online')
        ->and($check->http_code)->toBe(200)
        ->and($check->response_time_ms)->toBeGreaterThanOrEqual(0)
        ->and($check->website_id)->toBe($website->id);
});

test('check does not notify on the first-ever check', function () {
    Notification::fake();
    Http::fake(['*' => Http::response('ok', 200)]);

    $website = Website::factory()->create(['base_url' => 'https://example.test']);

    app(UptimeService::class)->check($website);

    Notification::assertNothingSent();
});

test('check notifies the owner when the website goes offline', function () {
    Notification::fake();

    $website = Website::factory()->create(['base_url' => 'https://example.test']);
    UptimeCheck::factory()->for($website)->create(['status' => 'online', 'checked_at' => now()->subMinute()]);

    Http::fake(function () {
        throw new Illuminate\Http\Client\ConnectionException('Connection refused');
    });

    app(UptimeService::class)->check($website);

    Notification::assertSentTo($website->user, WebsiteUptimeStatusChanged::class);
});

test('check notifies the owner when the website comes back online', function () {
    Notification::fake();

    $website = Website::factory()->create(['base_url' => 'https://example.test']);
    UptimeCheck::factory()->for($website)->create(['status' => 'offline', 'checked_at' => now()->subMinute()]);

    Http::fake(['*' => Http::response('ok', 200)]);

    app(UptimeService::class)->check($website);

    Notification::assertSentTo($website->user, WebsiteUptimeStatusChanged::class);
});

test('check does not notify when status is unchanged', function () {
    Notification::fake();

    $website = Website::factory()->create(['base_url' => 'https://example.test']);
    UptimeCheck::factory()->for($website)->create(['status' => 'online', 'checked_at' => now()->subMinute()]);

    Http::fake(['*' => Http::response('ok', 200)]);

    app(UptimeService::class)->check($website);

    Notification::assertNothingSent();
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
