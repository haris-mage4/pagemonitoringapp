<?php

namespace App\Jobs;

use App\Models\Website;
use App\Services\UptimeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckWebsiteUptimeJob implements ShouldQueue
{
    use Queueable;

    public int $timeout;

    public function __construct(public readonly Website $website)
    {
        $this->timeout = (int) config('pagespeed.uptime_check_timeout') + 10;
    }

    public function handle(UptimeService $uptime): void
    {
        $uptime->check($this->website);
    }
}
