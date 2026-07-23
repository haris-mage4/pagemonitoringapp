<?php

use App\Jobs\CheckWebsiteUptimeJob;
use App\Models\Website;
use Illuminate\Support\Facades\Bus;

test('dispatches an uptime check job for each enabled website only', function () {
    Bus::fake();

    $enabled = Website::factory()->create(['enabled' => true]);
    Website::factory()->create(['enabled' => false]);

    $this->artisan('pagespeed:check-uptime')->assertSuccessful();

    Bus::assertDispatchedTimes(CheckWebsiteUptimeJob::class, 1);
    Bus::assertDispatched(CheckWebsiteUptimeJob::class, fn ($job) => $job->website->is($enabled));
});
