<?php

use App\Jobs\ScanWebsiteJob;
use App\Models\Page;
use App\Models\Scan;
use App\Models\Website;
use Illuminate\Support\Facades\Bus;

test('does not redispatch a website whose scan is still pending in the queue', function () {
    Bus::fake();

    $website = Website::factory()->create(['enabled' => true, 'schedule' => 'hourly']);
    $page = Page::factory()->for($website)->create(['enabled' => true]);

    // Simulates a busy queue: the scan was dispatched a minute ago but hasn't run yet.
    Scan::factory()->for($page)->create([
        'status' => 'pending',
        'created_at' => now()->subMinute(),
    ]);

    $this->artisan('pagespeed:dispatch-scheduled-scans')->assertExitCode(0);

    Bus::assertNotDispatched(ScanWebsiteJob::class);
});

test('dispatches once the interval has actually elapsed since the last scan', function () {
    Bus::fake();

    $website = Website::factory()->create(['enabled' => true, 'schedule' => 'hourly']);
    $page = Page::factory()->for($website)->create(['enabled' => true]);

    Scan::factory()->for($page)->create([
        'status' => 'completed',
        'created_at' => now()->subHours(2),
    ]);

    $this->artisan('pagespeed:dispatch-scheduled-scans')->assertExitCode(0);

    Bus::assertDispatchedSync(ScanWebsiteJob::class);
});
