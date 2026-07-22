<?php

use App\Jobs\ScanWebsiteJob;
use App\Models\Website;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Bus;

test('handleDeployment dispatches a delayed manual scan job', function () {
    Bus::fake();

    $website = Website::factory()->create();

    app(WebhookService::class)->handleDeployment($website);

    Bus::assertDispatched(ScanWebsiteJob::class, function (ScanWebsiteJob $job) use ($website) {
        return $job->website->is($website)
            && $job->trigger === 'webhook'
            && $job->delay !== null;
    });
});
