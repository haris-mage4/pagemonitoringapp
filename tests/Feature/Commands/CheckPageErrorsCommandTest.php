<?php

use App\Jobs\CheckPageErrorsJob;
use App\Models\Page;
use App\Models\Website;
use Illuminate\Support\Facades\Bus;

test('dispatches a job for each enabled page on an enabled website only', function () {
    Bus::fake();

    $enabledWebsite = Website::factory()->create(['enabled' => true]);
    $disabledWebsite = Website::factory()->create(['enabled' => false]);

    $enabledPage = Page::factory()->for($enabledWebsite)->create(['enabled' => true]);
    Page::factory()->for($enabledWebsite)->create(['enabled' => false]);
    Page::factory()->for($disabledWebsite)->create(['enabled' => true]);

    $this->artisan('pagespeed:check-page-errors')->assertSuccessful();

    Bus::assertDispatchedTimes(CheckPageErrorsJob::class, 1);
    Bus::assertDispatched(CheckPageErrorsJob::class, fn ($job) => $job->page->is($enabledPage));
});
