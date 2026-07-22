<?php

use App\Jobs\ScanPageJob;
use App\Jobs\ScanWebsiteJob;
use App\Models\Page;
use App\Models\Website;
use Illuminate\Support\Facades\Bus;

test('handle dispatches a ScanPageJob for each enabled page only', function () {
    Bus::fake();

    $website = Website::factory()->create();
    $enabledPage = Page::factory()->for($website)->create(['enabled' => true]);
    Page::factory()->for($website)->create(['enabled' => false]);

    (new ScanWebsiteJob($website, 'manual'))->handle();

    Bus::assertDispatchedTimes(ScanPageJob::class, 1);
    Bus::assertDispatched(ScanPageJob::class, function (ScanPageJob $job) use ($enabledPage) {
        return $job->page->is($enabledPage) && $job->trigger === 'manual';
    });
});
