<?php

use App\Jobs\ScanPageJob;
use App\Models\Page;
use App\Services\ScanService;
use Illuminate\Queue\Middleware\WithoutOverlapping;

test('handle delegates to ScanService with the page and trigger', function () {
    $page = Page::factory()->create();

    $this->mock(ScanService::class, function ($mock) use ($page) {
        $mock->shouldReceive('scanPage')
            ->once()
            ->withArgs(fn ($p, $trigger) => $p->is($page) && $trigger === 'schedule');
    });

    app(ScanPageJob::class, ['page' => $page, 'trigger' => 'schedule'])
        ->handle(app(ScanService::class));
});

test('middleware limits concurrency to a single WithoutOverlapping lock by default', function () {
    $page = Page::factory()->create();

    $job = new ScanPageJob($page, 'manual');
    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(WithoutOverlapping::class);
});
