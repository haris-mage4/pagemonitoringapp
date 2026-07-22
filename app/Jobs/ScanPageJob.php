<?php

namespace App\Jobs;

use App\Models\Page;
use App\Services\ScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class ScanPageJob implements ShouldQueue
{
    use Queueable;

    public int $timeout;

    public function __construct(
        public readonly Page $page,
        public readonly string $trigger,
    ) {
        $this->timeout = (int) config('pagespeed.scan_timeout') + 30;
    }

    /**
     * Concurrency slot chosen from `pagespeed.concurrent_scans` — job id is hashed onto
     * one of N lock keys so at most N Lighthouse scans run at once (default 1).
     */
    public function middleware(): array
    {
        $slots = max(1, (int) config('pagespeed.concurrent_scans'));
        $slot = crc32($this->page->id.':'.$this->job?->getJobId()) % $slots;

        return [
            (new WithoutOverlapping("lighthouse-scan-slot-{$slot}"))
                ->releaseAfter(5)
                ->expireAfter($this->timeout),
        ];
    }

    public function handle(ScanService $scans): void
    {
        $scans->scanPage($this->page, $this->trigger);
    }
}
