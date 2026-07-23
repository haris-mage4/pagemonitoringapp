<?php

use App\Jobs\CheckWebsiteUptimeJob;
use App\Models\Website;
use App\Services\UptimeService;

test('handle delegates to UptimeService with the website', function () {
    $website = Website::factory()->create();

    $this->mock(UptimeService::class, function ($mock) use ($website) {
        $mock->shouldReceive('check')->once()->with(Mockery::on(fn ($arg) => $arg->is($website)));
    });

    (new CheckWebsiteUptimeJob($website))->handle(app(UptimeService::class));
});
